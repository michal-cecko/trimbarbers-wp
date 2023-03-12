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
        this.activeDate = this.getTodaysDate()

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
                appointment: {
                    customer: {},
                    datetime: {},
                    type: "free",
                    serviceID: "",
                },
            },
            created() {
                console.log(`Calendar Vue component has been created.`)
                this.initCalendar()
            },
            mounted() {
            },
            methods: {
                async initCalendar() {
                    let _thisVue = this

                    let appointments = await this.fetchAppointments();

                    document.addEventListener('DOMContentLoaded', function () {
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
                                // Create a new Bootstrap modal
                                let modal = new bootstrap.Modal(document.getElementById('createAppointmentModal'));
                                // Show the modal
                                modal.show();
                                _thisVue.appointment.datetime.start = info.start
                                _thisVue.appointment.datetime.end = info.end
                            },
                            selectOverlap: function (event) {
                                return event.rendering === 'background'; // only allow selection on background events
                            },
                            eventResizableFromStart: function (event) {
                                return event.rendering === 'background'; // only allow resizing of background events
                            },
                            eventDidMount: function (info) {
                                let deleteButtonHtml = '<span class="delete-button">&times;</span>';
                                let eventEl = info.el;
                                let deleteButtonEl = eventEl.querySelector('.delete-button');
                                if (!deleteButtonEl) {
                                    eventEl.insertAdjacentHTML('beforeend', deleteButtonHtml);
                                    deleteButtonEl = eventEl.querySelector('.delete-button');
                                    deleteButtonEl.addEventListener('click', function () {
                                        info.event.remove();
                                    });
                                }
                            },
                        })
                        this.calendar = calendar;
                        calendar.render()
                    })
                },

                async createAppointment() {

                    let data = new FormData();
                    data.append("barber_id", 2)
                    data.append("appointment", JSON.stringify(this.appointment))
                    data.append("action", "make_appointment")
                    data.append("nonce", _thisClass.nonce)

                    await fetch(_thisClass.ajaxURL, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: data,
                    }).then((response) => {
                        let data = response.json()
                        console.log(data)
                    }).catch((error) => {
                        console.error(error);
                    });
                },

                async fetchAppointments() {
                    let data = new FormData();
                    data.append("barber_id", 2)
                    data.append("datetime", (new Date()).toISOString())
                    data.append("action", "get_appointments")
                    data.append("nonce", _thisClass.nonce)

                    await fetch(_thisClass.ajaxURL, {
                        method: 'GET',
                        credentials: 'same-origin',
                        body: data,
                    }).then((response) => {
                        let data = response.json()
                        console.log(data)
                    }).catch((error) => {
                        console.error(error);
                    });
                },
            },
        });
    }
}

new ReservationCalendar()

export {}
