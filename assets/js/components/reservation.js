import Commons from "./commons.js"

class Reservation extends Commons {

    constructor() {
        super();
        this.init();
    }

    init() {
        let _Vue = Vue

        new _Vue({
                el: '#reservation',
                data: {
                    step: 1,
                    barber: null,
                    service: null,
                    date: null,
                    time: null,
                },
                created() {
                    console.log(`Reservation Vue component has been created.`)
                },
                methods: {
                    chooseBarber(id) {
                        this.barber = id
                        console.log(this.barber)
                    }
                },
            }
        );
    }
}

new Reservation()

export {}
