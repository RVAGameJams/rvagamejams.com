#!/usr/bin/env python3

import sys
from functools import wraps
from flask import Flask, render_template, request, redirect, make_response, url_for, session
from flask.sessions import session_json_serializer
from flask.ext.pymongo import PyMongo
from bson.json_util import dumps
from bson.objectid import ObjectId
from bson import binary
from hashlib import sha512
from datetime import datetime
from base64 import b64decode
import traceback
import json
import io

app = Flask(__name__)

# enable dev mode
if ( '-d' in sys.argv ) or ( "--dev" in sys.argv ):
    app.debug = True
    app.config['MONGO_DBNAME'] = 'test'
else:
    app.config['MONGO_DBNAME'] = 'production'

with open('secret_key') as key_file:
    app.secret_key = key_file.read()


# enable image uploads
UPLOAD_FOLDER = '/images'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif'])

# connect to the database
mongo = PyMongo(app)

#-- AUTHENTICATORS

# handle the login procedure
def do_login( username, password ):
    salt = app.secret_key #"ludumdare_secret_jammer"

    pass_hash = sha512( ( password + salt ).encode('utf-8') ).hexdigest()

    user_document = mongo.db.Users.find_one({"name": username})
    if user_document and user_document['password'] == pass_hash:
        session_id = mongo.db.Sessions.insert_one({"username": username, "createdAt": datetime.now()}).inserted_id
        session['name'] = username
        session['session_id'] = str(session_id)
        return True
    else:
        return False

def do_logout():
    mongo.db.Sessions.delete_one({"_id": ObjectId( session['session_id'] ) })
    session.clear()
    return


# check for authentication on pages that use it. Redirect to login on fail
def authenticate(group="user"): # outer wrapper to receive group argument
    def authenticator_wrapper(f): # inner wrapper to fix namespacing

        @wraps(f)
        def decorated_authenticator(*args, **kwargs): # where the magic happens
            redirect_object = redirect( url_for("login", next=request.url) )

            # redirect if no session exists
            if not 'session_id' in session or not 'name' in session:
                return redirect_object

            # see if the session id is in the DB and user has permissions
            session_document = mongo.db.Sessions.find_one({"_id": ObjectId( session['session_id'] ) })
            if session_document and session_document['username'] == session['name']:
                if group == "admin": # make sure admin pages have admin priveledges
                    global user
                    user_document = mongo.db.Users.find_one({ "name": session['name'] })
                    user = user_document["name"]

                    if user_document['group'] == "admin":
                        return f(*args, **kwargs)
                    else:
                        redirect_object
                else: # base user pages get a pass
                    return f(*args, **kwargs)

            else:
                return redirect_object

        return decorated_authenticator
    return authenticator_wrapper

def get_user(*args, **kwargs): # where the magic happens
    # see if the session id is in the DB and user has permissions
    if 'session_id' in session and 'name' in session:
        session_document = mongo.db.Sessions.find_one({"_id": ObjectId( session['session_id'] ) })
        if session_document and session_document['username'] == session['name']:
            user_document = mongo.db.Users.find_one({ "name": session['name'] })
            return {
                'name': session_document['username'],
                'group': user_document['group']
                }

    return None




#-- HANDLERS: apps

@app.route("/")
def index():
    return render_template( "index.html", user=get_user() )

@app.route("/admin", defaults={ 'panel': None } )
@app.route("/admin/<panel>")
@authenticate(group="admin")
def admin(panel):
    return render_template( "admin.html", user=get_user(), panel=panel )




#-- HANDLERS: ajax

@app.route("/login", methods=['POST', 'DELETE'])
def login():
    next_page = request.args['next'] if 'next' in request.args else "/"
    if request.method == "POST":
        if do_login( request.json["name"], request.json["password"] ):
            return make_response( json.dumps( get_user() ), 200)
        else:
            return make_response("", 403)
    if request.method == "DELETE":
        do_logout()
        return make_response("", 200)

@app.route("/admin/events", methods=['POST', 'PUT', 'DELETE'])
@authenticate(group="admin")
def admin_events():
    if request.method == 'POST':
        errors = []

        try:
            event_document = mongo.db.Events.insert_one({
                'name': request.json['name'],
                'start': datetime.fromtimestamp( int( request.json['start'] ) / 1000 ),
                'end': datetime.fromtimestamp( int( request.json['end'] ) / 1000 ),
                'headline': request.json['headline'],
                'description': request.json['description'],
                'cover-image': {
                    'type': request.json['cover_image']['type'],
                    'data': image_to_mongo( request.json['cover_image']['data'] )
                }
            })

            return make_response('', 200)
        except Exception as error:
            return make_response( str(error), 400)
    elif request.method == 'PUT':
        pass
    elif request.method == 'DELETE':
        pass

@app.route("/events", methods=['GET'])
def events():
    events = mongo.db.Events.find()
    return make_response( dumps(events), 200 )

@app.route("/images/<table_name>/<_id>/<field>", methods=['GET'])
def images(table_name, _id, field):
    _file = getattr(mongo.db, table_name.capitalize()).find_one({ '_id': ObjectId(_id) })[field]
    print(_file['type'])
    return make_response( _file['data'], 200, { "Content-Type": _file['type'] } )


# -- Helpers
def image_to_mongo( image ):
    return binary.Binary( bytes( b64decode( image ) ) )

#-- START APPLICATION

if __name__ == "__main__":
    app.run()
else:
    print("RVAGameJams.com Must be run stand alone.")
