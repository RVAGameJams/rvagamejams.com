#!/usr/bin/env python3

import sys
from flask import Flask, render_template, request, make_response, session
# from flask.sessions import session_json_serializer
from flask.ext.pymongo import PyMongo
from bson.json_util import dumps
from bson.objectid import ObjectId
from datetime import datetime
import json

from modules import helpers, authentication

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

#-- HANDLERS: apps

@app.route("/")
def index():
    return render_template( "index.html", user=helpers.get_user(mongo) )

@app.route("/admin", defaults={ 'panel': None } )
@app.route("/admin/<panel>")
@authentication.authenticate(mongo, group="admin")
def admin(panel):
    return render_template( "admin.html", user=helpers.get_user(mongo), panel=panel )




#-- HANDLERS: ajax

@app.route("/login", methods=['POST', 'DELETE'])
def login():
    next_page = request.args['next'] if 'next' in request.args else "/"
    if request.method == "POST":
        if authentication.login( request.json["name"], request.json["password"] ):
            return make_response( json.dumps( helpers.get_user(mongo) ), 200)
        else:
            return make_response("", 403)
    if request.method == "DELETE":
        authentication.logout()
        return make_response("", 200)

@app.route("/admin/events", methods=['POST', 'PUT', 'DELETE'])
@authentication.authenticate(mongo, group="admin")
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
                'cover-image': helpers.image_to_mongo( request.json['cover_image'] )
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

    return make_response( _file['data'], 200, { "Content-Type": _file['type'] } )


#-- START APPLICATION

if __name__ == "__main__":
    app.run()
else:
    print("RVAGameJams.com Must be run stand alone.")
