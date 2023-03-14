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
        const utcOffset = 60; // UTC+1
        const now = moment.utc().utcOffset(utcOffset);
        return now.valueOf();
    }

    leadZero(number) {
        if(number < 10) return "0" + number;
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
}