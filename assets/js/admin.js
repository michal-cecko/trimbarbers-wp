class Admin {
    constructor() {
        this.ajaxURL = PHPVars.ajaxUrl
        this.nonce = PHPVars.nonce
        this._prepareRemoveUnnecessarities()
    }

    _prepareRemoveUnnecessarities() {

        // Cleanup profile edit --- START

        let form = document.querySelector("#your-profile")
        if (form) {
            let headings = form.querySelectorAll("h2")
            let textsToRemove = ['Kontaktné údaje', 'O sebe', 'Meno', 'Správa účtu', 'Barber', 'Osobné nastavenia']
            headings.forEach(item => {
                let i = textsToRemove.indexOf(item.textContent)
                if (i !== -1) item.remove()
            })
        }


        // Cleanup profile edit --- END
    }
}

new Admin()