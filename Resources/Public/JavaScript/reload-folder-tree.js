class ReloadFolderTree {
    constructor() {
        window.addEventListener('load', () => {
            top.document.dispatchEvent(new CustomEvent('typo3:filestoragetree:refresh'));
        });
    }
}

export default new ReloadFolderTree();