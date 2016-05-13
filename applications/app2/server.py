from flask import Flask, request, render_template, Response
import requests
import json
app = Flask(__name__)

allowed_objects = [
    'user', 'note', 'phone'
]

def get(object, oid=None):
    path = object
    if oid is not None:
        path += "/%s" % oid
    resp = requests.get('http://www.api-ep.com/%s' % path)
    return resp.json()

def post(object, oid=None):
    path = object
    if oid is not None:
        path += "/%s" % oid
    resp = requests.post('http://www.api-ep.com/%s' % path, request.form)
    app.logger.debug(resp.text)
    return resp.json()

def delete(object, oid):
    pass

@app.route("/")
def main():
    users = get('@user')
    notes = get('@note')
    phones = get('@phone')
    return render_template('index.html', users=users, notes=notes, phones=phones)


@app.route("/<object>", methods=["GET", "POST", "DELETE"])
@app.route("/<object>/<int:oid>", methods=["GET", "POST", "DELETE"])
def rest_endpoint(object, oid=None):
    if object not in allowed_objects:
        return 'Not found', 404
    if request.method == 'GET':
        obj = get('@%s' % object, oid)
        return Response(json.dumps(obj),  mimetype='application/json')
    elif request.method == 'POST':
        obj = post('@%s' % object, oid)
        return Response(json.dumps(obj),  mimetype='application/json')
    elif request.method == 'DELETE':
        delete('@%s' % object, oid)
        return '', 204
    return '', 403