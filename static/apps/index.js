var RactiveApp = (function (Ractive) {
    'use strict';

    Ractive = 'default' in Ractive ? Ractive['default'] : Ractive;

    var component$1 = { exports: {} };

        component$1.exports = {
            data: {
                events: [],
                current_event: 0,
                show_description: false,

                get_date: function() {
                    return new Date()
                },
                render_markdown: function(text) {
                    return markdown.toHTML(text)
                }
            },

            oninit: function() {
                var that = this;

                superagent.get("/events")
                .end( function( error, response ) {
                    if (!error) {
                        var events = JSON.parse(response.text);

                        events.forEach( function(element) {
                            element.start = new Date( element.start['$date'] )
                            element.end = new Date( element.end['$date'] )
                        });
                        events.sort( function(a,b) {
                            var now = new Date()

                            if ( b.start < now && b.end > now ) {
                                return 1;
                            }
                            if ( a.start > b.start ) {
                                return -1;
                            }
                            else if ( a.start < b.start ) {
                                return 1;
                            }
                            else {
                                return 0;
                            }
                        })
                        that.set( 'events', events );
                    }
                });
            },

            rotate_event: function(amount) {
                var current_event = ( this.get('current_event') + amount ) % 5
                if ( current_event < 0 ) {
                    current_event = 5;
                }
                this.set( 'current_event', current_event )

            },

            transitions: {
                carousel: function(t) {
                    if ( t.isIntro ) {
                        t.setStyle({ "transform": "translateX(100%)" });
                        t.animateStyle({ "transform": "translateX(0%)" }, {'duration': 500}, function() { t.complete() });
                    }
                    else {
                        t.animateStyle({ "transform": "translateX(-100%)" }, {'duration': 500}, function() { t.complete() });
                    }
                }
            }
        }

    component$1.exports.template = {v:3,t:[{p:[1,1,0],t:7,e:"div",a:{"class":"container"},f:[{t:4,f:[{t:4,f:[{p:[4,13,89],t:7,e:"div",a:{"class":"event",style:[{t:2,x:{r:["show_description"],s:"_0?\"margin-top: -75.5px\":\"\""},p:[4,39,115]}," background-image: url(images/",{t:2,r:"_id.$oid",p:[4,118,194]},"-cover.png)"]},f:[{p:[5,17,236],t:7,e:"div",a:{"class":"headline"},f:[{p:[6,21,279],t:7,e:"h4",f:[{t:4,f:[{p:[8,29,363],t:7,e:"span",a:{style:"color: green"},f:["Upcoming:"]}],n:50,x:{r:["start","get_date"],s:"_0>_1()"},p:[7,25,308]},{t:4,n:51,f:[{t:4,n:50,x:{r:["start","get_date","end"],s:"_0<_1()&&_1()<_2"},f:[{p:[10,29,510],t:7,e:"span",a:{style:"color: yellow"},f:["Ongoing:"]}]},{t:4,n:50,x:{r:["start","get_date","end"],s:"!(_0<_1()&&_1()<_2)"},f:[" ",{p:[12,29,616],t:7,e:"span",a:{style:"color: red"},f:["Past:"]}]}],x:{r:["start","get_date"],s:"_0>_1()"}}," ",{t:2,r:"name",p:[14,25,711]}]}," ",{p:[16,21,766],t:7,e:"small",f:[{t:2,r:"start",p:[16,28,773]}]}," ",{p:[17,21,811],t:7,e:"p",f:[{t:2,r:"headline",p:[17,24,814]}]}," ",{t:4,f:[{p:[20,25,902],t:7,e:"button",a:{"class":"pure-button button-small"},v:{click:{m:"toggle",a:{r:[],s:"[\"show_description\"]"}}},f:["More…"]}],n:50,x:{r:["show_description"],s:"!_0"},p:[19,21,852]}]}]}," ",{t:4,f:[{p:[26,17,1120],t:7,e:"div",a:{"class":"description"},f:[{p:[27,21,1166],t:7,e:"button",a:{"class":"pure-button button-close"},v:{click:{m:"toggle",a:{r:[],s:"[\"show_description\"]"}}},f:["X"]}," ",{t:3,x:{r:["render_markdown","description"],s:"_0(_1)"},p:[29,21,1277]}]}],n:50,r:"show_description",p:[25,13,1079]}],n:50,x:{r:["current_event","i"],s:"_0==_1"},p:[3,9,50]}],i:"i",r:"events",p:[2,5,28]}," ",{t:4,f:[{p:[36,9,1426],t:7,e:"div",a:{"class":"nav"},f:[{p:[37,13,1456],t:7,e:"span",v:{click:{m:"rotate_event",a:{r:[],s:"[-1]"}}},f:["<"]},"   ",{p:[39,13,1535],t:7,e:"span",v:{click:{m:"rotate_event",a:{r:[],s:"[1]"}}},f:[">"]}]}],n:50,x:{r:["show_description"],s:"!_0"},p:[35,5,1392]}]}]};
    component$1.exports.css = ".container{width:100vw;min-height:200px;color:#fff}.event{background-color:#210;display:inline-block;width:100%;transition:.5s margin-top ease-out}.headline{width:400px;margin:20px;padding:10px;border:1px solid #fff;border-radius:8px}.button-small{font-size:80%}.button-close{padding:2px}.nav{margin-top:-1.5em;font-weight:bolder;user-select:none;cursor:pointer;width:100%;text-align:right}.description{opacity:.9;border:2px solid #fff;border-radius:8px;margin:10px;padding:10px;background:#eee;color:#000}";
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

    component.exports.template = {v:3,t:[{p:[4,1,110],t:7,e:"profile-toolbar"}," ",{p:[6,1,131],t:7,e:"div",f:[{p:[7,5,141],t:7,e:"h1",f:["RVAGameJams"]}]}," ",{p:[10,1,170],t:7,e:"carousel"}]};
    component.exports.components = { carousel: __import0__, "profile-toolbar": __import1__ };
    var index = Ractive.extend( component.exports );

    return index;

}(Ractive));