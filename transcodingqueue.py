#! /usr/bin/python
import time
import os
import requests
import sys

f = open('run.txt', 'w')
f.write("1")
f = open('videos.txt')
for line in f:
    args = line.split()
    os.system('ffmpeg -i ' + args[0] +' -vcodec libx264 ' + args[1] + ' 1> ' + args[2] + ' 2>&1')
    if args[0] != args[1]:
        os.system('rm ' + args[0])
    os.system('rm ' + args[2])
    #os.system(" curl -v -H 'x-prefix: vod' -H 'x-path: priv' -H 'x-subpath: /' -F upload=@" + args[1] + " 'https://" + args[5] + ":" + args[6] + "@" + args[7] + "'")
    requests.post('https://working_url/transcoding_ready/', data={'name':args[3], 'contact_id':args[4], 'video_id':args[8]})
    time.sleep(2)
lines = f.readlines()
lines = lines[:-1]
with open('videos.txt', 'w') as f:
    f.writelines(lines)
path = os.path.join(os.path.abspath(os.path.dirname(__file__)), 'run.txt')
os.remove(path)