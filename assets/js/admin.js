import Ajax from "./components/ajax";

class Admin extends Ajax{
    constructor() {
        super()

        this.ajaxURL = PHPVars.ajaxUrl
        this.nonce = PHPVars.nonce
    }
}

new Admin

export {};