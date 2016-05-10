#!/usr/bin/env python3

import sys
from functools import wraps
from flask import Flask, render_template, request, redirect, make_response, url_for, session
from flask.sessions import session_json_serializer
from flask.ext.pymongo import PyMongo
from bson.json_util import dumps
from bson.objectid import ObjectId
from hashlib import sha512
from datetime import datetime
import json

app = Flask(__name__)

# enable dev mode
if ( '-d' in sys.argv ) or ( "--dev" in sys.argv ):
    app.debug = True
    app.config['MONGO_DBNAME'] = 'test'
else:
    app.config['MONGO_DBNAME'] = 'production'

with open('secret_key') as key_file:
    app.secret_key = key_file.read()

# connect to the database
mongo = PyMongo(app)

#-- AUTHENTICATORS

# handle the login procedure
def do_login( username, password ):
    salt = app.secret_key #"ludumdare_secret_jammer"

    pass_hash = sha512( ( password + salt ).encode('utf-8') ).hexdigest()
    print( pass_hash )

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
# def authenticate(group="user"): # outer wrapper to receive group argument
#     def authenticator_wrapper(f): # inner wrapper to fix namespacing
#
#         @wraps(f)
#         def decorated_authenticator(*args, **kwargs): # where the magic happens
#             redirect_object = redirect( url_for("login", next=request.url) )
#
#             # redirect if no session exists
#             if not 'session_id' in session or not 'name' in session:
#                 return redirect_object
#
#             # see if the session id is in the DB and user has permissions
#             session_document = mongo.db.Sessions.find_one({"_id": ObjectId( session['session_id'] ) })
#             if session_document and session_document['username'] == session['name']:
#                 if group == "admin": # make sure admin pages have admin priveledges
#                     global user
#                     user_document = mongo.db.Users.find_one({ "name": session['name'] })
#                     user = user_document["name"]
#
#                     if user_document['group'] == "admin":
#                         return f(*args, **kwargs)
#                     else:
#                         redirect_object
#                 else: # base user pages get a pass
#                     return f(*args, **kwargs)
#
#             else:
#                 return redirect_object
#
#         return decorated_authenticator
#     return authenticator_wrapper

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

#-- HANDLERS

@app.route("/")
def index():
    admins = mongo.db.AdminUsers.find()
    return render_template("index.html", site_name="RVAGameJams", admins=admins, user=get_user())


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



#-- START APPLICATION

if __name__ == "__main__":
    app.run()
else:
    print("RVAGameJams.com Must be run stand alone.")