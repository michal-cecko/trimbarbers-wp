export default class Commons {
    constructor() {
        this.ajaxURL = PHPVars.ajaxUrl;
        this.nonce = PHPVars.nonce;
        this.phoneMQ = window.matchMedia('(max-width: 768px)');
    }

    getDateFromTimestamp(timestamp, addTime = false) {
        const date = moment(timestamp);
        return date.format('YYYY-MM-DD');
    }

    getCurrentTimestamp() {
        return this.utc(moment());
    }

    utc(datetime) {
        const utcOffset = 60; // UTC+1
        let x = moment(datetime).utc().utcOffset(utcOffset);
        return x.valueOf()
    }

    leadZero(number) {
        if (number < 10) return "0" + number;
        return number
    }

    addParamsToUrl(params, baseUrl) {
        const queryString = Object.entries(params)
            .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
            .join('&');

        return `${baseUrl}?${queryString}`;
    }

    async WPPostAjax(body) {
        return fetch(this.ajaxURL, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        });
    }

    validateEmail(email) {
        const re = /\S+@\S+\.\S+/;
        return re.test(email);
    }

    getDayName(num) {
        if (num === "1") return "Pon";
        if (num === "2") return "Uto";
        if (num === "3") return "Str";
        if (num === "4") return "Štv";
        if (num === "5") return "Pia";
        if (num === "6") return "Sob";
        return "Ned";
    }

    getMonthName(num) {
        if (num === "1") return "Január";
        if (num === "2") return "Február";
        if (num === "3") return "Marec";
        if (num === "4") return "Apríl";
        if (num === "5") return "Máj";
        if (num === "6") return "Jún";
        if (num === "7") return "Júl";
        if (num === "8") return "August";
        if (num === "9") return "September";
        if (num === "10") return "Október";
        if (num === "11") return "November";
        return "December";
    }

    empty(variable) {
        return ([undefined, null, false, 0, "[]", "", "null", "0000-00-00"].includes(variable)) ||
            (Array.isArray(variable) && !variable.length) ||
            (!Array.isArray(variable) && typeof variable === "object" && !Object.keys(variable).length)
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    eraseCookie(name) {
        document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
}