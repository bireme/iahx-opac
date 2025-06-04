/*
* functions.js
* version: 1.0.2
*/

// usado para fazer paginação
function go_to_page(page) {
    var form = document.searchForm;
    var count = document.searchForm.count.value;
    var from = (page*count)-count+1;

    form.from.value = from;
    form.page.value = page;

    $("#q").focus();            // prevent submit of default placeholder text
    $("#searchForm").submit();
    $("#q").blur();
}

// usado para mudar a forma de exibição do dado (xml/rss/print/site)
function change_output(output) {
    var form = document.searchForm;
    form.output.value = output;

    $("#searchForm").submit();
    form.output.value = "site"; //return to default output
}

// muda a quantidade de resultados exibidos
function change_count(count) {
    var form = document.searchForm;
    form.count.value = count;

    $("#searchForm").submit();
}

// muda o parâmetro lang
function change_language(lang) {
    if (RESULT_PAGE == true){
        $("#searchForm #lang").val(lang);
        $("#searchForm").submit();
    }else{
        $("#changeLanguageForm #change_lang").val(lang);
        $("#changeLanguageForm").submit();
    }
}

// muda a quantidade de resultados exibidos
function change_format(elem) {
    var form = document.searchForm;
    form.format.value = elem.value;

    $("#searchForm").submit();
}

function print_record(q) {
    var form = document.searchForm;
    form.q.value = q;
    print_page(1);
}

// altera output param para "print" e realiza o submit
function print_page(selection) {
    if (selection == true){
        $.get('bookmark/list', function(q) {
            $("#q").val(q);
            $("#from").val(0);
            $("#page").val("1");
            $("input[name^='filter']").remove();
            $("#output").val("print");
            $("#searchForm").submit();
        });
    }else{
        // close modal
        $('#ModalPrint').modal('toggle');
        window.print();
    }
}

function export_record(q) {
    var form = document.searchForm;
    form.q.value = q;
    export_result(1);
}

// leva para output "ris/citation/csv", passando o count
function export_result(count) {

    var form = document.searchForm;
    var previous_count = form.count.value;
    var output = getCheckedValue(document.exportForm.format);

    if(count)
        form.count.value = count;
    else
        form.count.value = -1;

    change_output(output);
    form.count.value = previous_count;    //return to previous value
}

function export_xml_record(id) {
    var form = document.searchForm;
    form.q.value = 'id:' + id;

    change_output('xml');
}

// advanced search
function decs_locator() {
    const decs_locator_url = SEARCH_URL + 'decs-locator/?lang=' + LANG;

    $("#searchForm").attr("action", decs_locator_url);
    $("#searchForm").submit();
}

function fill_range(interval){
    var currentTime = new Date();
    var year = currentTime.getFullYear();

    $("#range_start").val(year-interval);
    $("#range_end").val(year);

}


/**
 * Mostra janela com grafico do cluster selecionado
 * @param {Node} obj
 * @param {String} titulo
 * @param {String} id
 */
function open_chart(obj, titulo, id){
    var regex = /\(\d+\)/;
    var params= "";

    var grupo = document.getElementById("ul_" + id);
    var lista = grupo.getElementsByTagName('li');

    for (i = 0; i < lista.length; i++){
        cluster = lista[i].innerHTML;
        clusterLabel = lista[i].getElementsByTagName('a')[0].innerHTML;

        ma = regex.exec(cluster);
        if (ma != null) {
            clusterTotal = ma[0].replace(/[()]/g,'');
            params += "&l[]=" + clusterLabel.trim() + "&d[]=" + clusterTotal.trim();
        }
    }
    // caso seja o cluster de ano passa parametro para realizar sort
    if (id == 'year_cluster'){
        params += "&sort=true";
    }
    url = "chart/?title=" + titulo + params + "&KeepThis=true&TB_iframe=true&height=480&width=650";
    obj.href = url;
}

// return the value of the radio button that is checked
// return an empty string if none are checked, or
// there are no radio buttons
function getCheckedValue(radioObj) {
    if(!radioObj)
        return "";
    var radioLength = radioObj.length;
    if(radioLength == undefined)
        if(radioObj.checked)
            return radioObj.value;
        else
            return "";
    for(var i = 0; i < radioLength; i++) {
        if(radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function change_sort(obj){
    var sort = obj.options[obj.selectedIndex].value;
    var form = document.searchForm;

    form.sort.value = sort;
    $("#searchForm").submit();
}

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function send_email() {
  const fromEmail = document.getElementById("email_form_from_email");
  const toEmail = document.getElementById("email_form_to_email");

  if (
    !fromEmail.value.trim() ||
    !toEmail.value.trim() ||
    !isValidEmail(fromEmail.value) ||
    !isValidEmail(toEmail.value)
  ) {
    return false;
  } else {
    // Copy all input fields from searchForm to emailForm
    $("#searchForm input").each(function () {
      var input = $(this);
      var clonedInput = input.clone();
      $("#emailForm").append(clonedInput);
    });

    $("#emailForm").submit();
    return true;
  }
}