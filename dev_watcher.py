#!/usr/bin/env python3

from subprocess import Popen
import time


if __name__ == "__main__":
    process = Popen(["python3", "main.py", "-d"])

    while True:
        process.wait()
        process = Popen(["python3", "main.py", "-d"])
        time.sleep(1)
