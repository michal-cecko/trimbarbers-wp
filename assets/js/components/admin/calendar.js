import Commons from "../commons.js"
import {Calendar} from 'https://cdn.skypack.dev/@fullcalendar/core@6.1.4'
import timeGridPlugin from 'https://cdn.skypack.dev/@fullcalendar/timegrid@6.1.4'
import dayGridPlugin from 'https://cdn.skypack.dev/@fullcalendar/daygrid@6.1.4'
import interactionPlugin from 'https://cdn.skypack.dev/@fullcalendar/interaction@6.1.4'
import skLocale from 'https://cdn.skypack.dev/@fullcalendar/core/locales/sk'

class ReservationCalendar extends Commons {

    constructor() {
        super();

        this.activeClass = "active";
        this.activeDate = this.getDateFromTimestamp(this.getCurrentTimestamp())

        this.init();
    }

    init() {
        let _Vue = Vue
        let _thisClass = this
        new _Vue({
            el: '#calendarContainer',
            data: {
                calendar: null,

                //Data needed to create appointment
                appointment: {},
                freeAppColor: "#0b99a6",
                serviceColors: {},

                createModal: null,
                editModal: null,

                deleteModal: null,
                appointmentToDelete: null,

                loggedInBarber: null,
                chosenBarber: null,
                barbers: null,

                notify: false,
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

                this.loggedInBarber = document.getElementById('logged-user').dataset;
                this.chosenBarber = parseInt(this.loggedInBarber.id);
                this.barbers = JSON.parse(document.getElementById('barbers').dataset.barbers)

                this.serviceColors = JSON.parse(document.getElementById('services-colors').dataset.colors)
                this.initCalendar()
            },
            methods: {
                async initCalendar() {
                    let _thisVue = this

                    let now = _thisClass.getCurrentTimestamp()
                    let appointments = await this.fetchAppointments(now);

                    const calendarEl = document.getElementById('calendar')
                    let calendar = new Calendar(calendarEl, {
                        plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin],
                        locale: skLocale,

                        nowIndicator: true,
                        editable: true,
                        selectable: true,
                        initialView: 'timeGridWeek',

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

                        // Creating events
                        select: function (info) {
                            // Show the modal
                            _thisVue.createModal.show();
                            _thisVue.appointment.datetime.start = moment(info.start).format('YYYY-MM-DD HH:mm:ss')
                            _thisVue.appointment.datetime.end = moment(info.end).format('YYYY-MM-DD HH:mm:ss')
                        },
                        selectOverlap: function (event) {
                            return true //event.rendering === 'background'; // only allow selection on background events
                        },
                        eventResizableFromStart: function (event) {
                            return true //event.rendering === 'background'; // only allow resizing of background events
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
                                    let event = info.event;
                                    console.log(info.event)
                                    _thisVue.appointmentToDelete = info.event
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
                        eventClick: function(info) {
                            if (info.jsEvent.target.classList.contains('delete-button')) {
                                return false;
                            }
                            _thisVue.loadEditModal(info.event);
                            console.log(this.appointment);
                            _thisVue.editModal.show();
                        },
                        eventDrop: function (info) {
                            let app = info.event
                            _thisVue.moveAppointment(app.extendedProps.id, moment(app.start).format('YYYY-MM-DD HH:mm:ss'), moment(app.end).format('YYYY-MM-DD HH:mm:ss'))
                        },
                        eventResize: function (info) {
                            let app = info.event
                            _thisVue.moveAppointment(app.extendedProps.id, moment(app.start).format('YYYY-MM-DD HH:mm:ss'), moment(app.end).format('YYYY-MM-DD HH:mm:ss'))
                        },
                        events: appointments
                    })
                    this.calendar = calendar;
                    calendar.render()
                },

                async createAppointment() {
                    let data = new FormData();
                    let barberID = parseInt(this.loggedInBarber.id);
                    data.append("barberID", barberID)
                    data.append("appointment", JSON.stringify(this.appointment))
                    data.append("action", "make_appointment")
                    data.append("nonce", _thisClass.nonce)

                    try {
                        let response = await _thisClass.WPPostAjax(data);
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
                        this.createModal.hide();
                        return true;
                    } catch (error) {
                        console.error(error);
                        return false;
                    }
                },

                async removeAppointment() {
                    let data = new FormData();
                    data.append("id", this.appointmentToDelete.extendedProps.id)
                    data.append("action", "remove_appointment")
                    data.append("nonce", _thisClass.nonce)
                    try {
                        let response = await _thisClass.WPPostAjax(data)
                        response = await response.json();
                        this.appointmentToDelete.remove()
                        this.deleteModal.hide();
                        console.log(response)
                    } catch (error) {
                        console.error(error);
                        return null;
                    }
                },

                async moveAppointment(id, newStart, newEnd) {
                    let data = new FormData();
                    data.append("id", id)
                    data.append("newStart", newStart)
                    data.append("newEnd", newEnd)
                    data.append("action", "move_appointment")
                    data.append("nonce", _thisClass.nonce)

                    try {
                        let response = await _thisClass.WPPostAjax(data);
                        response = await response.json();
                        console.log(response)
                    } catch (error) {
                        console.error(error);
                        return null;
                    }
                },

                async fetchAppointments(timestamp, dateRange = "week", barberID = 2) {
                    let params = {
                        barberID: barberID,
                        timestamp: timestamp,
                        dateRange: dateRange,
                        action: "get_appointments",
                        nonce: _thisClass.nonce,
                    };
                    try {
                        let response = await fetch(_thisClass.addParamsToUrl(params, _thisClass.ajaxURL));
                        response = await response.json();

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

                        return appointments
                    } catch (error) {
                        console.error(error);
                        return null;
                    }
                },

                changeCurrentBarberView(id) {
                    this.chosenBarber = id
                },

                loadEditModal(appToEdit) {
                    this.resetAppointmentVariable()
                    let type = appToEdit.extendedProps.type
                    console.log(appToEdit.extendedProps)
                    if(type !== "free") {
                        this.appointment.customer = appToEdit.extendedProps.customer
                        this.appointment.serviceID = appToEdit.extendedProps.serviceID
                    }
                    this.appointment.type = appToEdit.extendedProps.type
                    this.appointment.note = appToEdit.extendedProps.note
                    this.appointment.datetime = {
                        start: appToEdit.start,
                        end: appToEdit.end
                    }
                },

                resetAppointmentVariable() {
                    this.appointment = {
                        customer: {},
                        datetime: {},
                        type: "free",
                        serviceID: "",
                    }
                },

                resetAppointmentToDeleteVariable() {
                    this.appointmentToDelete = null
                },

                getActiveColor(appointmentType, serviceID) {
                    return appointmentType === "free" ? this.freeAppColor : this.serviceColors[serviceID]
                }
            },
        });
    }
}

new ReservationCalendar()

export {}
