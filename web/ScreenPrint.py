import os
from flask import Flask, jsonify, redirect, render_template, url_for, request
import time
#from urllib import request

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'static/uploads'

#DEPLOYMENT_PATH = '/screenprint'
DEPLOYMENT_PATH = ''

patient_id = 'Patient Zero'
timestamp = time.strftime("%Y-%m-%d %H:%M:%S")

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ['png', 'jpg']

@app.route('/')
def index():
    return 'A quick NHS Hack Day project'

@app.route('/upload', methods=['GET', 'POST'])
def upload():
    if request.method == 'POST':
        file = request.files['image']
        if file and allowed_file(file.filename):

            filename = 'image.png'
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))

            global patient_id
            global timestamp

            patient_id = request.form['patientId']
            timestamp = request.form['timestamp']

            return redirect(DEPLOYMENT_PATH + url_for('show'))

    return render_template('upload.html', deployment_path=DEPLOYMENT_PATH)

@app.route('/show', methods=['GET'])
def show():
    return render_template('show.html', patient=patient_id, timestamp=timestamp, image='image.png', deployment_path=DEPLOYMENT_PATH)

#@app.route('/price/<string:brand_str>', methods=['GET'])
#def price(brand_str):
#    page = request.urlopen('http://www.mysupermarket.co.uk/Shopping/FindProducts.aspx?Query=' + brand_str)
#    html = page.read()

    return html

if __name__ == '__main__':
    app.run(port=11101)
