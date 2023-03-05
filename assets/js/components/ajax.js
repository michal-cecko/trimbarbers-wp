export default class Ajax {
    constructor() {
        this.ajaxURL = PHPVars.ajaxUrl;
        this.nonce = PHPVars.nonce;
        this.phoneMQ = window.matchMedia('(max-width: 768px)');
    }
}