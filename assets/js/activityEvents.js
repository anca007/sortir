const getCities = function (e) {

    //récupération de l'url d'appel et de la valeur du code postal
    let url = $(this).data('url');
    let zipCode = $(this).val();

    //Fetch ajax
    fetch(url, {method: 'POST', body: JSON.stringify({"zipcode": zipCode})})
        .then(function (response) {
            return response.json()
        }).then(function (data) {

        //on vide le select
        $('#location_city').empty();

        //on y ajout les villes récupérées en fonction du code postal
        if (data.length > 0) {
            let options = '';

            $.each(data, function (idx, val) {
                options += "<option value='" + val.id + "'>" + val.name + "</option>";
            });
            $('#location_city').append(options);
        }
        syncCitySelect();
    });
}

const getLocation = function (e) {

    //récupération des données
    let address = $('#location_street').val();
    let city = $('#location_city').val();
    let url = $('#location_city').data('url');

    //Fetch ajax pour mise à jour du nom de rue, latitude et longitude
    fetch(url, {method: 'POST', body: JSON.stringify({"address": address, 'city': city})})
        .then(function (response) {
            return response.json()
        }).then(function (data) {

        $('#location_latitude').val(data.lat);
        $('#location_longitude').val(data.lng);
        $('#location_street').val(data.street);
    });

}

const createLocation = function (e) {
    e.preventDefault();

    //test si champs required vides ou pas
    let required = true;
    $(".modal-body [required]").each(function (idx, elem) {
        if ($(elem).val().trim() == '') {
            required = false;
        }
    });
    if (!required) {
        document.querySelector('#locationForm').reportValidity();
        return false;
    }

    //récupération de l'url
    let url = $('#locationForm').attr('action');
    //création d'un objet FormData pour soumettre le form en ajax
    let formData = new FormData(document.querySelector("#locationForm"));

    fetch(url, {method: 'POST', body: formData})
        .then(function (response) {
            return response.text();
        }).then(function (data) {

        //test pour parser en json la réponse si c'est bon c'est qu'on récupère bien notre lieu
        //sinon c'est que le formulaire n'est pas valide
        try {
            data = JSON.parse(data);
            //ajout du lieu nouvellement créé à la liste de base en le sélectionnant
            $('#activity_location').append("<option value='" + data.id + "' selected>" + data.name + "</option>");
            $('#locationModal').modal('hide');
            $('.modal-body input').val("");

        } catch (e) {
            $('.modal-body').empty();
            $('.modal-body').append(data);
            //ajout des events sur les boutons, vu que l'on a remplacé tout le body de la modal
            $('#location_zipCode').on('keyup', delay(getCities, 500));
            $('#searchCoord').on('click', getLocation);
        }

    });

}

const getLocations = function (e) {

    let url = $(this).data('url');
    let city = $(this).val();

    //récupère les lieux par rapport à la ville
    fetch(url, {method: 'POST', body: JSON.stringify({'city': city})})
        .then(function (response) {
            return response.json();
        }).then(function (data) {
        //on vide le select et on rajoute les lieux spécifiques au lieu
        $('#activity_location').empty();
        $.each(data, function (idx, val) {
            $('#activity_location').append('<option value="' + val.id + '">' + val.name + '</option>')
        })
    })
}


const syncCitySelect = function (){

    let id = $('#location_city').val();
    $('#activity_city').val(id);
    $('#activity_city').trigger('change');
}

//ajout des events sur les boutons
$('#location_zipCode').on('keyup', delay(getCities, 500));
$('#searchCoord').on('click', getLocation);
$('#locationCreate').on('click', createLocation);
$('#location_city').on('change', syncCitySelect);

$('#activity_city').on('change', getLocations);


//méthode permettant de ne pas lancer tout de suite un évenement
function delay(fn, ms) {
    let timer = 0
    return function (...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
    }
}


