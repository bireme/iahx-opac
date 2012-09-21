function add_new_line(obj) {
    var block = $(".block-q");
    var html = block.html();
    var more = $(".more");

    more.append(html);
}

function new_line(obj) {
    if(obj.value.length < 1) {
        add_new_line();        
    }
}

function add_query(current, q, bool, index) {
    q = '"'+q+'"';
    current = current + " " + bool + " (";

    if(index === "") {
        current = current + q;
    } else {
        current = current + index + ':' + q + '';
    }

    current = current + ")";
    return current;
}

function search() {
    var search_form = document.search_form;
    var form = document.advanced;
    var q = form["q[]"];
    var index = form['index[]'];
    var bool = form['bool[]'];
    var query = "";

    if(q[0].value != "") {
        query = add_query("", q[0].value, "", index[0].value);
        for(i=0; i<=bool.length; i++) {
            if(q[i+1] && q[i+1].value != "") {
                query = add_query(query, q[i+1].value, bool[i].value, index[i+1].value);
            }
        }
    }

    search_form.q.value = query;
    search_form.submit();

    return false;
}