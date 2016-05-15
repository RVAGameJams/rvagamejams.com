from base64 import b64decode
from bson import binary
from flask import session
from bson.objectid import ObjectId



def image_to_mongo( file_info ):
    file_info['data'] = binary.Binary( bytes( b64decode( file_info['data'] ) ) )
    return file_info

def get_user(mongo, *args, **kwargs): # where the magic happens
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
