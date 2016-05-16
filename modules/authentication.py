from functools import wraps
from hashlib import sha512
from flask import session, make_response
from bson.objectid import ObjectId
from datetime import datetime

# handle the login procedure
def login( username, password, app, mongo ):
    salt = app.secret_key

    pass_hash = sha512( ( password + salt ).encode('utf-8') ).hexdigest()

    user_document = mongo.db.Users.find_one({"name": username})
    if user_document and user_document['password'] == pass_hash:
        session_id = mongo.db.Sessions.insert_one({"username": username, "createdAt": datetime.now()}).inserted_id
        session['name'] = username
        session['session_id'] = str(session_id)
        return True
    else:
        return False

def logout():
    mongo.db.Sessions.delete_one({"_id": ObjectId( session['session_id'] ) })
    session.clear()
    return


# check for authentication on pages that use it. Redirect to login on fail
def authenticate(mongo, group="user"): # outer wrapper to receive group argument
    def authenticator_wrapper(f): # inner wrapper to fix namespacing

        @wraps(f)
        def decorated_authenticator(*args, **kwargs): # where the magic happens
            # return error if no session exists
            if not 'session_id' in session or not 'name' in session:
                # TODO add error page
                return make_response("", 403)

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
