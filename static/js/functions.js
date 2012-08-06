// usado para fazer paginação
function go_to_page(page) {
    var form = document.searchForm;
    var count = document.searchForm.count.value;
    var from = (page*count)-count+1;
    
    form.from.value = from;
    form.page.value = page;
    form.submit();
}

// usado para mudar a forma de exibição do dado (xml/rss/print/site)
function change_output(output) {
    var form = document.searchForm;
    form.output.value = output;

    form.submit();
}

// muda a quantidade de resultados exibidos
function change_count(elem) {
    var form = document.searchForm;
    form.count.value = elem.value;

    form.submit();   
}

// muda o parâmetro lang
function change_language(lang) {
    document.searchForm.lang.value = lang;
    document.searchForm.submit();
}

// leva para output "print", passando o count
function print(count) {

    var form = document.searchForm;
    if(count)
        form.count.value = count;
    else 
        form.count.value = 300;

    change_output("print");
}

// leva para output "ris/citation", passando o count
function export_result(count) {

    var form = document.searchForm;
    var output = getCheckedValue(document.exportForm.format);

    alert(output);

    if(count)
        form.count.value = count;
    else 
        form.count.value = 300;

    change_output(output);
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
            params += "&l[]=" + clusterLabel + "&d[]=" + clusterTotal;
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