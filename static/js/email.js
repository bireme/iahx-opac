function send_email() {
    var form = document.emailForm;

    if( form["email[]"].value === "") {
        alert("Todos os campos com asterisco (*) devem ser preenchidos.");
        return false;
    }

    if( form["email[]"].value.search("@") < 0) {
        alert("Por favor, preencha com emails válidos.");
        return false;   
    }
    
    return true;
}

function add_more_email() {
    var dest = $("#destinatarios");
    var input = dest.html();
    var more_dest = $("#more-destinatarios");

    more_dest.html(more_dest.html() + input);
}