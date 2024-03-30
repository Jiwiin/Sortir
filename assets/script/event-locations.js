
//Dynamiser l'adresse et le code postal
    console.log("jsuis dans le script")
    const locationSelect = document.querySelector('#event_location');
    const streetDisplay = document.getElementById('location-street');
    const zipcodeDisplay = document.getElementById('location-zipcode');

    const initialValue = locationSelect.value;

    streetDisplay.textContent = locationsData[initialValue].street;
    zipcodeDisplay.textContent = locationsData[initialValue].zipcode;

    locationSelect.addEventListener('change', function() {

    const selectedLocationId = this.value;
    const locationInfo = locationsData[selectedLocationId];

        if (locationInfo) {
        streetDisplay.textContent = locationInfo.street;
        zipcodeDisplay.textContent = locationInfo.zipcode;
        }
    });

