import Commons from "./commons.js"

class Reservation extends Commons {

    constructor() {
        super();
        this.init();
    }

    init() {
        let _Vue = Vue
        let _thisClass = this

        new _Vue({
                el: '#reservation',
                data: {
                    step: 1,

                    barber: {
                        id: null,
                        name: null,
                        photo: null,
                    },

                    service: {},

                    date: null,
                    time: null,

                    customer: {
                        name: "",
                        email: "",
                        phone: "",
                        note: "",
                    },

                    errors: [],

                    saveCustomerToCookies: false,

                    availableDates: {},
                    timeOptions: {},

                    datesLoading: false,
                    sending: false,
                    sent: false,

                    isVisibleOrder: false,
                    isModalVisible: false,

                    container: null,
                    contentContainer: null,
                    cards: {},
                },
                created() {
                    console.log(`Reservation Vue component has been created.`)
                    this.defaultService()
                },
                mounted() {
                    if (!this.container) this.container = document.querySelector(".reservation-container")
                    if (!this.contentContainer) this.contentContainer = document.querySelector(".content-container")
                    let cards = this.container.querySelectorAll(".content")

                    if (this.container && cards.length) {
                        for (let i = 1; i < cards.length + 1; i++) {
                            this.cards[i] = cards[i - 1];
                        }
                        this.changeHeight(1)
                    }

                    window.resize = function () {
                        this.checkCustomStyle()
                    }
                },
                methods: {
                    changeHeight(step) {
                        this.container.style.height = (this.cards[step].offsetHeight + 160) + "px"
                        console.log(this.cards[step], this.container.style.height)
                    },
                    chooseBarber(id, name, image) {
                        this.isVisibleOrder = true
                        this.barber.id = id
                        this.barber.name = name
                        this.barber.photo = image
                    },
                    chooseService(id, name, price) {
                        this.isVisibleOrder = true
                        this.service.id = id
                        this.service.name = name
                        this.service.price = price
                    },
                    chooseDate(date) {
                        this.isVisibleOrder = true
                        this.date = date
                        this.timeOptions = this.availableDates[moment(date).format("M")][date]['apps']
                    },
                    chooseTime(time) {
                        this.isVisibleOrder = true
                        this.chosenTime = {...this.timeOptions[time], time: time};
                    },
                    async changeStep(nextStep, returning = false) {
                        if (!this.canContinue(nextStep)) return false;

                        this.step = nextStep
                        if (nextStep !== 5) {
                            this.isVisibleOrder = false
                        }

                        if (nextStep === 3) {
                            if(!returning) {
                                await this.loadDates()
                            } else {
                                this.chosenTime = null
                            }
                        }

                        this.checkCustomStyle()
                        this.changeHeight(nextStep)
                    },
                    formatSelectedDatetime() {
                        let final = ""
                        final += moment(this.date).format('D. MMMM YYYY')
                        if (!_thisClass.empty(this.chosenTime)) {
                            final += ', ' + moment('2022-03-21T' + this.chosenTime.time + ':00').format('H:mm')
                        }
                        return final
                    },
                    empty(variable) {
                        return _thisClass.empty(variable)
                    },
                    canContinue(nextStep) {
                        //Naspäť možeš vždy
                        if (nextStep < this.step) return true;

                        //Checknuť barbera
                        if (nextStep === 2) {
                            if (!this.barber.id) return false
                        }
                        //Checknuť službu
                        else if (nextStep === 3) {
                            if (!this.service.id) return false
                        }
                        //Checknuť dátum
                        else if (nextStep === 4) {
                            if (!this.date) return false
                        }
                        //Checknuť čas
                        else if (nextStep === 5) {
                            if (_thisClass.empty(this.chosenTime)) return false
                        }
                        //Checknuť kontaktné údaje
                        else if (nextStep === 6) {
                            if (!this.customer.name || !this.customer.phone || !this.customer.email) return false
                        }
                        return true
                    },
                    loadDates() {
                        this.availableDates = {}
                        let params = {
                            barberID: this.barber.id,
                            serviceID: this.service.id,
                            action: "get_available_dates",
                            nonce: _thisClass.nonce,
                        };
                        this.datesLoading = true;
                        return fetch(_thisClass.addParamsToUrl(params, _thisClass.ajaxURL))
                            .then(response => response.json())
                            .then(response => {
                                this.datesLoading = false;

                                if (!response.dates) {
                                    console.error("Nastala chyba pri fetchovaní dátumov.");
                                    return;
                                }

                                this.availableDates = response.dates
                                console.log(response)
                            })
                    },
                    sanitizeName() {
                        let removeExisting = this.errors.indexOf("name");
                        if (removeExisting !== -1) {
                            this.errors.splice(removeExisting);
                        }
                        if (!this.customer.name.trim().length) {
                            this.errors.push("name")
                            return false;
                        }
                        return true;
                    },
                    isWeekend(date) {
                        return moment(date).day() === 6 || moment(date).day() === 0
                    },
                    sanitizePhone() {
                        let removeExisting = this.errors.indexOf("phone");
                        if (removeExisting !== -1) {
                            this.errors.splice(removeExisting);
                        }
                        if (this.customer.phone.trim().length < 9) {
                            this.errors.push("phone")
                            return false;
                        }
                        return true;
                    },
                    sanitizeEmail() {
                        let removeExisting = this.errors.indexOf("email");
                        if (removeExisting !== -1) {
                            this.errors.splice(removeExisting);
                        }
                        if (!_thisClass.validateEmail(this.customer.email)) {
                            this.errors.push("email")
                            return false;
                        }
                        return true;
                    },
                    sanitizeInputs() {
                        this.sanitizeName();
                        this.sanitizePhone();
                        this.sanitizeEmail();

                        if (!this.barber.id || !this.service.id || !this.chosenTime || !this.date) return false;

                        return !this.errors.length;

                    },
                    headerToggler() {
                        if (this.step !== 5) this.isVisibleOrder = !this.isVisibleOrder
                    },

                    async makeReservation() {
                        if (!this.sanitizeInputs()) {
                            return false;
                        }

                        let data = new FormData();
                        data.append("customer", JSON.stringify(this.customer))
                        data.append("barbers", JSON.stringify(this.chosenTime.barbers))
                        data.append("service", this.service.id)
                        data.append("date", this.date)
                        data.append("time", this.chosenTime.time)
                        data.append("action", "make_reservation")
                        data.append("nonce", _thisClass.nonce)
                        this.sending = true;
                        try {
                            let response = await _thisClass.WPPostAjax(data);
                            console.log(response)
                            if (response.status !== 200) {
                                this.sending = false;
                                return false;
                            }
                            console.log(response)
                            let clickIcon = document.querySelector(".check")
                            if (clickIcon) clickIcon.click()
                            response = await response.json();
                            this.sent = true;
                            await _thisClass.delay(1500);
                            this.isModalVisible = false;
                            await _thisClass.delay(500);
                            this.resetReservation()
                        } catch (error) {
                            console.error(error);
                            return null;
                        }
                    },
                    hasError(name) {
                        return this.step === 5 && this.errors.indexOf(name) !== -1
                    },
                    defaultService() {
                        this.service = {
                            id: null,
                            name: null,
                            price: null,
                        }
                    },
                    defaultBarber() {
                        this.barber = {
                            id: null,
                            name: null,
                            photo: null,
                        }
                    },
                    defaultCustomer() {
                        this.customer = {
                            name: "",
                            email: "",
                            phone: "",
                            note: "",
                        }
                    },
                    getDayName(num) {
                        return _thisClass.getDayName(num)
                    },
                    getMonthName(num) {
                        return _thisClass.getMonthName(num)
                    },
                    getTimeClass(time) {
                        return ""
                    },
                    resetReservation() {
                        this.defaultService()
                        this.defaultBarber()
                        this.defaultCustomer()
                        this.changeStep(1, true)
                        this.isVisibleOrder = false;
                        this.errors = [];
                        this.sent = false;
                        this.sending = false;
                        this.saveCustomerToCookies = false
                        this.availableDates = {}
                        this.chosenTime = null
                        this.date = null
                        this.timeOptions = {}
                    },
                    checkCustomStyle() {
                        let val = ((this.step - 1) * 20.3);
                        let style = "";
                        if (_thisClass.phoneMQ.matches) {
                            if (!this.container) this.container = document.querySelector(".reservation-container")
                            let width = this.container.offsetWidth
                            val = ((this.step - 1) * (width - 27.2));
                            style = 'translateX(-' + val + 'px)'
                        } else {
                            style = 'translateX(-' + val + 'rem)'
                        }
                        this.contentContainer.style.transform = style;
                        console.log(this.contentContainer.style.transform)
                    }
                },
                watch: {
                    barber: {
                        handler(newBarber, oldBarber) {
                            this.defaultService();
                        },
                        deep: true
                    },
                    service: {
                        handler(newService, oldService) {
                            this.chosenTime = null
                            this.date = null
                        },
                        deep: true
                    },
                    'customer.name'(newValue) {
                        this.sanitizeName()
                    },
                    'customer.email'(newValue) {
                        this.sanitizeEmail()
                    },
                    'customer.phone'(newValue) {
                        this.sanitizePhone()
                    }
                },
            }
        );
    }
}

new Reservation()

export {}
