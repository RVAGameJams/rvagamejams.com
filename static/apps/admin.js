var RactiveApp = (function (Ractive) {
    'use strict';

    Ractive = 'default' in Ractive ? Ractive['default'] : Ractive;

    var component$1 = { exports: {} };

        component$1.exports = {
            data: {
                name: '',
                start: '',
                end: '',
                headline: '',
                description: '',
                cover_image_binary: null,

                cover_image: null,
                cover_image_file: null,
                description_tab: 'markdown'
            },

            onchange: function(changes) {
                if ( 'description' in changes ) {
                    this.set( 'description_rendered', markdown.toHTML( changes['description'] ) )
                }
                if ( 'cover_image_file' in changes ) {
                    var that = this;

                    RGJ.helpers.render_image( changes.cover_image_file[0], function(result) {
                        that.set('cover_image', result);
                    } );
                    RGJ.helpers.encode_file( changes.cover_image_file[0], function(result) {
                        that.set('cover_image', result);
                    } );
                }
            },

            submit: function() {
                var data = {
                    name: this.get('name'),
                    cover_image: this.get('cover_image_binary'),
                    start: Date.parse( this.get('start') ),
                    end: Date.parse( this.get('end') ),
                    headline: this.get('headline'),
                    description: this.get('description')
                }

                superagent.post('/admin/events')
                .send(data)
                .end( function( error, response ) {

                });
            }
        }

    component$1.exports.template = {v:3,t:[{p:[1,1,0],t:7,e:"form",a:{"class":"sub-panel pure-form pure-form-stacked"},f:[{p:[2,5,57],t:7,e:"input",a:{placeholder:"Event Name",value:[{t:2,r:"name",p:[2,44,96]}]}}," ",{p:[3,5,111],t:7,e:"span",f:["Cover Image"]},{p:[3,30,136],t:7,e:"input",a:{type:"file",id:"cover-image",value:[{t:2,r:"cover_image_file",p:[3,73,179]}]}}," ",{p:[4,5,206],t:7,e:"img",a:{src:[{t:2,r:"cover_image",p:[4,15,216]}]}}," ",{p:[5,5,238],t:7,e:"input",a:{placeholder:"Start Time",value:[{t:2,r:"start",p:[5,44,277]}]}}," ",{p:[6,5,293],t:7,e:"input",a:{placeholder:"End Time",value:[{t:2,r:"end",p:[6,42,330]}]}}," ",{p:[7,5,344],t:7,e:"textarea",a:{"class":"headline",placeholder:"Headline",value:[{t:2,r:"headline",p:[7,62,401]}]}}," ",{p:[8,5,431],t:7,e:"div",f:[{p:[9,9,445],t:7,e:"div",a:{"class":["tab ",{t:2,x:{r:["description_tab"],s:"_0==\"markdown\"?\"active\":\"\""},p:[9,25,461]}]},v:{click:{m:"set",a:{r:[],s:"[\"description_tab\",\"markdown\"]"}}},f:["Markdown"]}," ",{p:[10,9,583],t:7,e:"div",a:{"class":["tab ",{t:2,x:{r:["description_tab"],s:"_0==\"rendered\"?\"active\":\"\""},p:[10,25,599]}]},v:{click:{m:"set",a:{r:[],s:"[\"description_tab\",\"rendered\"]"}}},f:["Rendered"]}," ",{t:4,f:[{p:[13,13,772],t:7,e:"textarea",a:{"class":"description",placeholder:"Description",value:[{t:2,r:"description",p:[13,75,834]}]}}],n:50,x:{r:["description_tab"],s:"_0==\"markdown\""},p:[12,9,722]},{t:4,n:51,f:[{p:[15,13,891],t:7,e:"div",a:{"class":"description-rendered"},f:[{t:3,r:"description_rendered",p:[16,17,942]}]}],x:{r:["description_tab"],s:"_0==\"markdown\""}}]}]}," ",{p:[21,1,1023],t:7,e:"button",a:{"class":"pure-button"},v:{click:{m:"submit",a:{r:[],s:"[]"}}},f:["Add Event"]}]};
    component$1.exports.css = "input{width:250px}textarea{width:500px;resize:none}.headline{height:4em}.description{height:12em;margin-top:0}.tab{display:inline-block;width:100px;padding:5px;margin-top:10px;text-align:center;font-variant:small-caps;user-select:none;cursor:pointer}.tab.active{border-bottom:2px solid #000}.description-rendered{background-color:#fff;width:100%;padding:10px}";
    var __import0__ = Ractive.extend( component$1.exports );

    var component$2 = { exports: {} };

        component$2.exports = {
            data: {
                is_open: false,
                show_error: false,
                is_logged_in: false,
                new_username: '',
                new_password: ''
            },
            oninit: function() {
                this.set( 'is_logged_in', this.get("user.name") != "" )
            },
            onchange: function(changes) {
                if ( 'is_open' in changes && changes['is_open'] === false ) {
                    this.set('show_error', false);
                }
            },
            log_in: function() {
                var that = this;
                superagent.post("/login")
                .send({
                    name: this.get('new_username'),
                    password: this.get('new_password')
                })
                .end( function(error, response) {
                    if ( !error ) {
                        that.set('user', JSON.parse( response.text ) )
                        that.set('is_logged_in', true)
                        that.set('is_open', false)
                    }
                    else {
                        that.set('show_error', true)
                    }
                });
            },
            log_out: function() {
                var that = this;
                superagent.delete("/login")
                .end( function(error, response) {
                    if ( !error ) {
                        that.set('user.name', '')
                        that.set('is_logged_in', false)
                        that.set('is_open', false)
                    }
                });
            }
        }

    component$2.exports.template = {v:3,t:[{p:[1,1,0],t:7,e:"div",a:{id:"profile-toolbar"},f:[{p:[2,5,31],t:7,e:"div",a:{id:"toggle"},f:[{p:[3,9,57],t:7,e:"a",v:{click:{m:"toggle",a:{r:[],s:"[\"is_open\"]"}}},f:[{t:2,x:{r:["user.name"],s:"_0!=\"\"?_0:\"Log-In\""},p:[3,41,89]}]}]}," ",{t:4,f:[{p:[7,9,178],t:7,e:"div",a:{id:"panel"},f:[{t:4,f:[{p:[9,17,242],t:7,e:"strong",f:["Username or password is incorrect"]}],n:50,r:"show_error",p:[8,13,207]}," ",{t:4,f:[{t:4,f:[{p:[13,21,412],t:7,e:"div",a:{"class":"menu-item"},f:[{p:[14,25,460],t:7,e:"a",a:{href:"/admin"},f:["Admin"]}]}],n:50,x:{r:["user.group"],s:"_0==\"admin\""},p:[12,17,362]}," ",{p:[17,17,554],t:7,e:"button",a:{"class":"pure-button"},v:{click:{m:"log_out",a:{r:[],s:"[]"}}},f:["Log-out"]}],n:50,r:"is_logged_in",p:[11,13,325]},{t:4,n:51,f:[{p:[19,17,657],t:7,e:"form",a:{"class":"pure-form pure-form-stacked"},f:[{p:[20,21,720],t:7,e:"input",a:{placeholder:"Jammer Name",value:[{t:2,r:"new_username",p:[20,61,760]}]}}," ",{p:[21,21,799],t:7,e:"input",a:{type:"password",placeholder:"Password",value:[{t:2,r:"new_password",p:[21,74,852]}]}}]}," ",{p:[23,17,911],t:7,e:"button",a:{"class":"pure-button"},v:{click:{m:"log_in",a:{r:[],s:"[]"}}},f:["Log-in"]}],r:"is_logged_in"}]}],n:50,r:"is_open",p:[6,5,154]}]}]};
    component$2.exports.css = "#panel,#toggle{background-color:#bbb;padding:10px}#profile-toolbar{position:absolute;right:0;top:0;display:flex;flex-direction:column}#toggle{display:inline-block;margin-left:auto}a{cursor:pointer;user-select:none}.menu-item{padding:5px}.menu-item a{color:#00f;text-decoration:none}";
    var __import1__ = Ractive.extend( component$2.exports );

    var component = { exports: {} };

    component.exports.template = {v:3,t:[{p:[4,1,113],t:7,e:"profile-toolbar"}," ",{p:[6,1,134],t:7,e:"div",a:{id:"body"},f:[{p:[7,5,154],t:7,e:"h1",f:["Admin"]}," ",{p:[8,5,173],t:7,e:"div",a:{id:"control-panel"},f:[{p:[9,9,206],t:7,e:"div",a:{id:"panel-select"},f:[{p:[10,13,242],t:7,e:"a",a:{"class":[{t:2,x:{r:["active_panel"],s:"_0==\"events\"?\"active\":\"\""},p:[10,24,253]}]},f:["Events",{p:[10,78,307],t:7,e:"a",f:[]}]}]}," ",{p:[12,9,334],t:7,e:"div",a:{id:"control-area"},f:[{t:4,f:[{p:[14,17,419],t:7,e:"events-panel"}],n:50,x:{r:["active_panel"],s:"_0==\"events\""},p:[13,13,370]}]}]}]}]};
    component.exports.css = "#control-area,#panel-select a.active{background-color:#eee}#body{display:flex;flex-direction:column;height:100vh}#control-panel{background-color:#ddd;flex-grow:1;display:flex;flex-direction:row}#panel-select{width:200px}#panel-select a{display:inline-block;font-size:26px;font-weight:700;padding:10px;user-select:none;cursor:pointer;width:100%}#control-area{flex-grow:1;padding:20px}";
    component.exports.components = { "events-panel": __import0__, "profile-toolbar": __import1__ };
    var admin = Ractive.extend( component.exports );

    return admin;

}(Ractive));