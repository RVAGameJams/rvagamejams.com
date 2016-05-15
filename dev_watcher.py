#!/usr/bin/env python3

from subprocess import Popen
import time


if __name__ == "__main__":
    Popen(["watch", "rollup -c --input components/admin.html --output=static/apps/admin.js && rollup -c --input components/index.html --output=static/apps/index.js", "components", "--interval=0.5"])
    process = Popen(["python3", "main.py", "-d"])

    while True:
        process.wait()
        process = Popen(["python3", "main.py", "-d"])
        time.sleep(1)
