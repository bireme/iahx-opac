$(document).ready(
    function(){
        $('div.record div.data').each(
            function(){
                //var author = $(this).find('div.author').html();
                var author = $(this).find('div.author').text();
                author = author.replace(/[^ ]+/i,'');
                var title = $(this).find('h3 > a').html();
                if (title == null){
                    title = $(this).find('h3').html();
                }

                var id = $(this).parent().attr('id');
                var loc = location.href;

                if ( loc.indexOf('?') > 0 ){
                    loc = loc.substring(0,loc.indexOf('?'));
                }

                var url = loc.replace(/#?$/i,'');

                // monta url para pagina de detalhes do recurso caso o usuario não esteja nela
                if ( loc.indexOf('resource/') == -1) {
                    //url = loc.replace(/([a-z]+\.php)?#?$/i,'resource/'+id);
                    var url = $(this).find('h3 > a').attr('href');
                }
                var source = window.location.hostname;

                var x = $(this).parent().find('div.user-actions div.platserv a.add-collection');

                var obj = new Object();
                obj.url = $.trim(url);
                obj.source = $.trim(source);
                obj.author = $.trim(author);
                obj.title = $.trim(title);
                obj.id = $.trim(id);
                obj.userTK = unescape(getCookie('userTK'));
                
                //alert(JSON.stringify(obj, null, 4));

                x.click( function(){
                  $.post(SERVICES_PLATFORM_DOMAIN + '/client/controller/servicesplatform/control/business/task/addDoc', obj, function(data){
                      response = $.parseJSON(data);

                      if(data == true){
                          alert(ADD_TO_COLLECTION_SUCCESS);
                      }else if(typeof response == 'object'){
                          alert(COLLECTION_EXISTS);
                      }else{
                          alert(ADD_TO_COLLECTION_ERROR);
                      }
                  });
                });
            }
        )

        function getCookie(name) {
          var value = "; " + document.cookie;
          var parts = value.split("; " + name + "=");
          if (parts.length == 2) return parts.pop().split(";").shift();
        }
    }
);
