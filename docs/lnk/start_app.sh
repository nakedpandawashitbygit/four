#!/bin/bash
source /home/h406470147/h406470147.nichost.ru/docs/lnk/virt_name/bin/activate
cd /home/h406470147/h406470147.nichost.ru/docs/lnk/
gunicorn --bind 0.0.0.0:8000 app:app
