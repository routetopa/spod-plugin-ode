/**
 * Created by Luigi Serra on 10/06/2015.
 */

var ComponentService =
{
	deep_url:"",
    data:"",
    query:"",
    component:"",
    link:"",
    getComponent: function(params){//params = {component, data, fields, placeHolder}
        var request = this.getXMLHttpRequest();
        this.component = params.component;
        this.data      = params.data;

        request.onreadystatechange = function(){
            if(request.readyState == 4){
                try {
                    var resp = JSON.parse(request.response);
                    this.link = '<link rel="import" href="' + resp.bridge_link + resp.component_link + '">';
                    //Build jsonPath query string
                    this.query     = "";
                    for(var i=0;i < params.fields.length;i++){
                        var query_elements = params.fields[i].split(',');
                        this.query += "$";
                        for(var j=0; j < query_elements.length - 1;j++){
                            this.query += "['" + query_elements[j] + "']";
                        }
                        this.query += "[*]" + "['" + query_elements[query_elements.length - 1] + "']";
                        this.query += "###";
                    }
                    this.query = this.query.substring(0, this.query.length - 3);

                    //Build datalet injecting html code
                    datalet_code = this.link + '<' + params.component;
                    var keys = Object.keys(params.params);
                    for(var i = 0; i < keys.length; i++){
                       datalet_code += ' ' + keys[i] + '="' + params.params[keys[i]] +'"';
                    }
                    datalet_code += ' query="' + this.query + '"></' + params.component + '>';

                    (params.placeHolder.constructor == HTMLElement) ? $(params.placeHolder).html(datalet_code) :/*Injection from Web Component*/
                                                                 $("#" + params.placeHolder).html(datalet_code);/*Injection from a static web page*/

                } catch (e){
                    var resp = {
                        status: 'error',
                        data: 'Unknown error occurred: [' + request.response + ']'
                    };
                }
            }};

        request.open('GET', this.deep_url + this.component);
        request.send();
    },

    getXMLHttpRequest: function ()
    {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        }
        else {
            try {
                return new ActiveXObject("MSXML2.XMLHTTP.3.0");
            }
            catch(ex) {
                return null;
            }
        }
    }
}
