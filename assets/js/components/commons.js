export default class Commons {
    constructor() {
        this.ajaxURL = PHPVars.ajaxUrl;
        this.nonce = PHPVars.nonce;
        this.phoneMQ = window.matchMedia('(max-width: 768px)');
    }

    getTodaysDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    leadZero(number) {
        if(number < 10) return "0" + number;
        return number
    }
}