
import LinkBrowser from "@typo3/backend/link-browser.js";
import RegularEvent from "@typo3/core/event/regular-event.js";
import { FileListActionEvent } from "@typo3/filelist/file-list-actions.js";
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import InfoWindow from "@typo3/backend/info-window.js";
import Notification from "@typo3/backend/notification.js";


class PdflipFileHandler {
  constructor() {
    new RegularEvent(FileListActionEvent.primary, (event) => {
      event.preventDefault();
      const detail = event.detail;
      detail.action = FileListActionEvent.select;
      document.dispatchEvent(new CustomEvent(FileListActionEvent.select, { detail: detail }));
    }).bindTo(document);
    new RegularEvent(FileListActionEvent.select, (event) => {
      event.preventDefault();
      const detail = event.detail;
      const resource = detail.resources[0];
      if (resource.type === 'file') {
        this.insertLink(resource);
      }
    }).bindTo(document);
    new RegularEvent(FileListActionEvent.show, (event) => {
      event.preventDefault();
      const detail = event.detail;
      const resource = detail.resources[0];
      InfoWindow.showItem('_' + resource.type.toUpperCase(), resource.identifier);
    }).bindTo(document);
  }
  insertLink(resource) {
    const request = new AjaxRequest(TYPO3.settings.ajaxUrls.link_resource);
    request.post({
      identifier: resource.identifier,
    }).then(async (success) => {
      const data = await success.resolve();
      data.status.forEach((message) => {
        Notification.showMessage(message.title, message.message, message.severity);
      });
      if (data.success) {
        LinkBrowser.finalizeFunction("t3://pdflip"+ data.link.substring(9));
      }
    });
  }

}
export default new PdflipFileHandler();
