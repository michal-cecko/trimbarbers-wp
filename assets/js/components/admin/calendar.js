import Commons from "../commons.js"

const {Calendar} = window.FullCalendar

class ReservationCalendar extends Commons {

    constructor() {
        super();

        this.activeClass = "active";
        this.activeDate = this.getDateFromTimestamp(this.getCurrentTimestamp())

        this.init();
        this._prepareAutoRefresh()
    }

    init() {
        let _Vue = Vue
        let _thisClass = this

        Vue.directive('click-outside', {
            bind: function (el, binding, vnode) {
                el.clickOutsideEvent = function (event) {
                    if (!(el == event.target || el.contains(event.target))) {
                        console.log("clicked out")
                        vnode.context[binding.expression](event);
                    }
                };
                document.body.addEventListener('click', el.clickOutsideEvent);
            },
            unbind: function (el) {
                document.body.removeEventListener('click', el.clickOutsideEvent);
            }
        });

        new _Vue({
            el: '#calendarContainer',
            data: {
                calendar: null,

                //Data needed to create appointment
                appointment: {},
                freeAppColor: "#0b99a6",
                serviceColors: {},
                serviceDurations: {},

                createModal: null,
                editModal: null,
                editingAppointment: null,

                deleteModal: null,
                appointmentToDelete: null,

                loggedInBarber: {},
                chosenBarberOnView: null,
                chosenBarberInForms: null,
                barbers: null,

                buttonLoader: false,

                dateRange: {start: null, end: null},
                customers: {},
                customerSearchQuery: "",
                shownOptions: false,
                debounceTimer: null,

                notify: false,

                hasInit: false,
            },
            created() {
                console.log(`Calendar Vue component has been created.`)
                this.resetAppointmentVariable()
                this.resetAppointmentToDeleteVariable()
            },
            mounted() {
                this.createModal = new bootstrap.Modal(document.getElementById('createAppointmentModal'))
                this.editModal = new bootstrap.Modal(document.getElementById('editAppointmentModal'))
                this.deleteModal = new bootstrap.Modal(document.getElementById('deleteAppointmentModal'))

                let barbers = JSON.parse(document.getElementById('barbers').dataset.barbers)
                this.barbers = Object.assign({}, barbers);

                this.loggedInBarber = document.getElementById('logged-user').dataset;
                let loggedInId = parseInt(this.loggedInBarber.id);
                if (this.loggedInBarber.role === "administrator" || !this.barbers[loggedInId]) {
                    this.chosenBarberOnView = -1;
                    const firstKey = Object.keys(this.barbers)[0];
                    if (this.barbers[firstKey]) {
                        this.chosenBarberInForms = this.barbers[firstKey].id;
                    }
                } else {
                    this.chosenBarberOnView = this.chosenBarberInForms = loggedInId;
                }

                this.serviceColors = JSON.parse(document.getElementById('services-colors').dataset.colors)
                this.serviceDurations = JSON.parse(document.getElementById('services-durations').dataset.durations)

                this.initCalendar()
            },
            methods: {
                hideOptions() {
                    this.shownOptions = false
                },

                async initCalendar() {
                    let _thisVue = this

                    let now = _thisClass.getCurrentTimestamp()
                    let appointments = await this.fetchAppointments(now, this.chosenBarberOnView, "timeGridWeek");

                    const calendarEl = document.getElementById('calendar')
                    let calendar = new Calendar(calendarEl, {
                        locale: 'sk',
                        nowIndicator: true,
                        select: function (info) {
                            // Show the modal
                            _thisVue.resetModals()
                            _thisVue.appointment.datetime = {
                                start: moment(info.start).format("YYYY-MM-DD HH:mm:ss"),
                                end: moment(info.end).format("YYYY-MM-DD HH:mm:ss"),
                            }
                            _thisVue.createModal.show();
                        },
                        longPressDelay: 1000,
                        editable: false,
                        selectable: true,
                        selectOverlap: true,
                        eventResizableFromStart: false,
                        initialView: 'timeGridWeek',
                        rerenderDelay: 500,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'timeGridWeek,timeGridDay'
                        },

                        views: {
                            timeGrid: {
                                dayHeaderFormat: {
                                    weekday: 'long',
                                    month: 'numeric',
                                    day: 'numeric',
                                    omitCommas: true
                                },
                                slotDuration: '00:15:00', // set slotDuration to 15 minutes
                                slotMinTime: '05:00:00', // set minimum time to 5am
                                slotMaxTime: '22:00:00', // set maximum time to 10pm
                            },
                        },

                        slotLabelFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        },

                        dayHeaderContent: function (arg) {
                            const date = new Date(arg.date);
                            const day = date.toLocaleString('sk-SK', {weekday: 'long'});
                            const month = date.toLocaleString('sk-SK', {month: 'numeric'});
                            const dayNum = date.toLocaleString('sk-SK', {day: 'numeric'});
                            return `${day}, ${dayNum}${month}`;
                        },

                        eventDidMount: function (info) {
                            let html = '<span class="delete-button">&times;</span>';
                            let eventEl = info.el;

                            let deleteButtonEl = eventEl.querySelector('.delete-button');
                            if (!deleteButtonEl) {
                                eventEl.insertAdjacentHTML('beforeend', html);
                                deleteButtonEl = eventEl.querySelector('.delete-button');
                                deleteButtonEl.addEventListener('click', function () {
                                    _thisVue.resetAppointmentToDeleteVariable()
                                    _thisVue.appointmentToDelete = info.event
                                    console.log(_thisVue.appointmentToDelete)
                                    _thisVue.deleteModal.show();
                                });
                            }
                        },

                        eventContent: function (info) {
                            let event = info.event;
                            let view = info.view.type;
                            let props = event.extendedProps;

                            let html = '<div class="event-content-container ' + view + '"><div class="time">' + moment(event.start).format('HH:mm') + ' - ' + moment(event.end).format('HH:mm') + '</div>';
                            html += '<div class="title">' + event.title + '</div>';

                            if (_thisVue.chosenBarberOnView === -1) {
                                if (_thisVue.barbers[props.barberID])
                                    html += '<div class="barber">barber: ' + _thisVue.barbers[props.barberID].name + '</div>';
                            }

                            if (props.type === "free") {

                            } else {
                                html += '<div class="service">' + props.service + '</div>';
                                if (view === "timeGridWeek") {
                                }
                                //day
                                else {
                                    html += '<h3 class="heading">Kontaktné údaje</h3>'
                                    html += '<div class="customer">';
                                    html += '<div class="email">' + props.customer.email + '</div>'
                                    html += '<div class="phone">' + props.customer.phone + '</div>'
                                    html += '</div>';
                                }
                            }
                            if (props.note) {
                                let note = props.note
                                if (note.length > 30 && view === "timeGridWeek") {
                                    note = note.substring(0, 30) + "...";
                                }
                                html += '<div class="note">' + note + '</div>';
                            }
                            html += '</div>';

                            return {
                                html: html,
                            };
                        },
                        eventClick: function (info) {
                            if (info.jsEvent.target.classList.contains('delete-button')) {
                                return false;
                            }
                            _thisVue.editingAppointment = info.event;
                            _thisVue.loadEditModal(info.event);
                            _thisVue.editModal.show();
                        },
                        eventDrop: false,
                        eventResize: false,
                        events: appointments,
                    })
                    calendar.on('datesSet', async function (info) {
                        if (!_thisVue.hasInit) return;

                        let start = calendar.view.currentStart
                        let end = calendar.view.currentEnd

                        _thisVue.dateRange.start = start
                        _thisVue.dateRange.end = end

                        let fetchStart = _thisClass.utc(moment(start).add(1, "hour"))
                        let appointments = await _thisVue.fetchAppointments(fetchStart, _thisVue.chosenBarberOnView, calendar.view.type)
                        _thisVue.exchangeAppointmentsOnView(appointments)
                    });
                    this.calendar = calendar;
                    calendar.render()

                    _thisVue.dateRange.start = calendar.view.currentStart
                    _thisVue.dateRange.end = calendar.view.currentEnd

                    this.hasInit = true;
                },

                async createAppointment() {
                    let data = new FormData();
                    let barberID = parseInt(this.chosenBarberInForms);
                    data.append("notify", this.notify)
                    data.append("barberID", barberID)
                    data.append("appointment", JSON.stringify(this.appointment))
                    data.append("action", "make_appointment")
                    data.append("nonce", _thisClass.nonce)

                    try {
                        this.buttonLoader = true;
                        let response = await _thisClass.WPPostAjax(data);
                        if (!response.ok) {
                            console.error("make_appointment failed:", response.status, await response.text());
                            this.buttonLoader = false;
                            return false;
                        }
                        let responseData = await response.json();

                        this.calendar.addEvent({
                            title: this.appointment.type === "free" ? "Voľno" : this.appointment.customer.name,
                            start: this.appointment.datetime.start,
                            end: this.appointment.datetime.end,
                            extendedProps: {
                                id: responseData.id,
                                type: this.appointment.type,
                                note: this.appointment.note,
                                service: responseData.service,
                                serviceID: this.appointment.serviceID,
                                barber: this.loggedInBarber.name,
                                barberID: barberID,
                                customer: this.appointment.customer,
                            },
                            color: this.getActiveColor(this.appointment.type, this.appointment.serviceID),
                            textColor: '#ffffff'
                        });
                        this.buttonLoader = false;

                        this.createModal.hide();
                        this.resetModals()

                        return true;
                    } catch (error) {
                        console.error(error);
                        this.buttonLoader = false;
                        return false;
                    }
                },

                async editAppointment() {
                    if (!this.editingAppointment) return;

                    let data = new FormData();
                    let barberID = parseInt(this.chosenBarberInForms);

                    data.append("id", this.editingAppointment.extendedProps.id)
                    data.append("notify", this.notify)
                    data.append("barberID", barberID)
                    data.append("appointment", JSON.stringify(this.appointment))
                    data.append("action", "edit_appointment")
                    data.append("nonce", _thisClass.nonce)

                    try {
                        this.buttonLoader = true;
                        let response = await _thisClass.WPPostAjax(data);
                        if (!response.ok) {
                            console.error("edit_appointment failed:", response.status, await response.text());
                            this.buttonLoader = false;
                            return false;
                        }
                        let responseData = await response.json();

                        this.editingAppointment.remove();
                        this.editingAppointment = null;

                        this.calendar.addEvent({
                            title: this.appointment.type === "free" ? "Voľno" : this.appointment.customer.name,
                            start: this.appointment.datetime.start,
                            end: this.appointment.datetime.end,
                            extendedProps: {
                                id: responseData.id,
                                type: this.appointment.type,
                                note: this.appointment.note,
                                service: responseData.service,
                                serviceID: this.appointment.serviceID,
                                barber: this.loggedInBarber.name,
                                barberID: barberID,
                                customer: this.appointment.customer,
                            },
                            color: this.getActiveColor(this.appointment.type, this.appointment.serviceID),
                            textColor: '#ffffff'
                        });

                        this.buttonLoader = false;
                        this.editModal.hide();
                        this.resetModals()

                        return true;
                    } catch (error) {
                        console.error(error);
                        this.buttonLoader = false;
                        return false;
                    }
                },

                async removeAppointment() {
                    let data = new FormData();
                    data.append("id", this.appointmentToDelete.extendedProps.id)
                    data.append("notify", this.notify)
                    data.append("action", "remove_appointment")
                    data.append("nonce", _thisClass.nonce)
                    try {
                        this.buttonLoader = true;
                        let response = await _thisClass.WPPostAjax(data)
                        if (!response.ok) {
                            console.error("remove_appointment failed:", response.status, await response.text());
                            this.buttonLoader = false;
                            return null;
                        }
                        this.appointmentToDelete.remove()
                        this.deleteModal.hide();
                        this.notify = false;
                        this.buttonLoader = false;
                    } catch (error) {
                        console.error(error);
                        this.buttonLoader = false;
                        return null;
                    }
                },

                fetchAppointments(timestamp, barberID, dateRange = "timeGridWeek") {
                    let params = {
                        barberID: barberID,
                        timestamp: timestamp,
                        dateRange: dateRange,
                        action: "get_appointments",
                        nonce: _thisClass.nonce,
                    };
                    console.log(params)
                    return fetch(_thisClass.addParamsToUrl(params, _thisClass.ajaxURL))
                        .then(response => response.json())
                        .then(response => {
                            let appointments = [];
                            for (const [ID, appointment] of Object.entries(response.appointments)) {
                                let title = "Voľno"
                                if (appointment.type !== "free") title = appointment.customer.name

                                appointments.push({
                                    title: title,
                                    start: moment(appointment.datetime.from, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DDTHH:mm:ss"),
                                    end: moment(appointment.datetime.to, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DDTHH:mm:ss"),
                                    extendedProps: {
                                        id: ID,
                                        type: appointment.type,
                                        note: appointment.note,
                                        service: appointment.service,
                                        serviceID: appointment.serviceID,
                                        barber: appointment.barber,
                                        barberID: appointment.barberID,
                                        customer: appointment.customer,
                                    },
                                    color: this.getActiveColor(appointment.type, appointment.serviceID),
                                    textColor: '#ffffff'
                                })
                            }

                            console.log(appointments)

                            return appointments;
                        })
                        .catch(error => {
                            console.error(error);
                            return null;
                        });
                },

                debouncedFetchCustomers() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.fetchCustomers()
                    }, 500);
                },

                fetchCustomers() {
                    const searchTerm = this.customerSearchQuery;
                    if (!searchTerm) {
                        this.customers = {};
                        return;
                    }
                    let params = {
                        search: searchTerm,
                        action: "get_customers",
                        nonce: _thisClass.nonce,
                    };
                    return fetch(_thisClass.addParamsToUrl(params, _thisClass.ajaxURL))
                        .then(response => response.json())
                        .then(response => {
                            this.customers = response.data;
                        })
                },

                chooseCustomer(customer) {
                    console.log("chosen")
                    this.shownOptions = false
                    this.customerSearchQuery = customer.name
                    this.appointment.customer.id = customer.id
                    this.appointment.customer.name = customer.name
                    this.appointment.customer.email = customer.email
                    this.appointment.customer.phone = customer.phone
                },

                formatCustomerOption(customer) {
                    let toReturn = "<b>" + customer.name + "</b>";
                    if (customer.email) {
                        toReturn += " | <span>" + customer.email + "</span>";
                    }
                    if (customer.phone) {
                        toReturn += " | <span>" + customer.phone + "</span>";
                    }
                    return toReturn
                },

                async changeCurrentBarberView(id) {
                    this.chosenBarberOnView = this.chosenBarberInForms = id

                    let now = moment(this.dateRange.start).add(1, "hour").valueOf()
                    let appointments = await this.fetchAppointments(now, id, this.calendar.view.type);
                    this.exchangeAppointmentsOnView(appointments)
                },

                exchangeAppointmentsOnView(appointments) {
                    let _thisVue = this
                    this.removeAppsFromViewOnly()
                    appointments.forEach(function (event) {
                        _thisVue.calendar.addEvent(event);
                    });
                },

                removeAppsFromViewOnly() {
                    let oldAppointments = this.calendar.getEvents();
                    oldAppointments.forEach(function (event) {
                        event.remove();
                    });
                },

                loadEditModal(appToEdit) {
                    this.resetAppointmentVariable()
                    let type = appToEdit.extendedProps.type

                    if (type !== "free") {
                        this.appointment.customer = {...appToEdit.extendedProps.customer}
                        this.appointment.serviceID = appToEdit.extendedProps.serviceID
                    }
                    this.chosenBarberInForms = appToEdit.extendedProps.barberID
                    this.appointment.type = appToEdit.extendedProps.type
                    this.appointment.note = appToEdit.extendedProps.note
                    this.appointment.datetime = {
                        start: moment(appToEdit.start).format("YYYY-MM-DD HH:mm:ss"),
                        end: moment(appToEdit.end).format("YYYY-MM-DD HH:mm:ss"),
                    }
                    console.log(this.appointment.datetime)
                },

                resetAppointmentVariable() {
                    this.appointment = {
                        customer: {},
                        datetime: {},
                        type: "appointment",
                        serviceID: document.getElementById('service').dataset.defaultvalue,
                    }
                },

                resetModals() {
                    this.resetAppointmentVariable()
                    this.resetAppointmentToDeleteVariable()
                    this.customerSearchQuery = ""
                    this.customers = {}
                    this.notify = false;
                },

                resetAppointmentToDeleteVariable() {
                    this.appointmentToDelete = null
                },

                getActiveColor(appointmentType, serviceID) {
                    return appointmentType === "free" ? this.freeAppColor : this.serviceColors[serviceID]
                }
            },
            watch: {
                'appointment.serviceID'(newID) {
                    let start = this.appointment.datetime.start
                    if (start && this.appointment.type === "appointment") {
                        this.appointment.datetime.end = moment(start, 'YYYY-MM-DD HH:mm:ss').add(parseInt(this.serviceDurations[newID]), "minutes").format("YYYY-MM-DD HH:mm:ss")
                    }
                },
                'appointment.datetime.start'(newStart) {
                    let start = this.appointment.datetime.start
                    let serviceID = this.appointment.serviceID
                    if (serviceID && start && this.appointment.type === "appointment") {
                        this.appointment.datetime.end = moment(start, 'YYYY-MM-DD HH:mm:ss').add(parseInt(this.serviceDurations[serviceID]), "minutes").format("YYYY-MM-DD HH:mm:ss")
                    }
                },
            }
        });
    }

    _prepareAutoRefresh() {
        function autoRefresh() {
            let timeoutID = setTimeout(() => {
                location.reload();
            }, 600000); //10 mins

            // Reset the timer if the user interacts with the page
            document.addEventListener("click", resetTimer);
            document.addEventListener("mousemove", resetTimer);
            document.addEventListener("touchstart", resetTimer);

            function resetTimer() {
                clearTimeout(timeoutID);
                timeoutID = setTimeout(() => {
                    location.reload();
                }, 600000); //10 mins
            }
        }

        autoRefresh();
    }
}

new ReservationCalendar()

export {}
