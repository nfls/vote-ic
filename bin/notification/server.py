from celery import Celery
from celery.schedules import crontab
import requests
from aliyunsms import AliyunSMS
import config
import json

app = Celery('notification', broker="redis://127.0.0.1", backend="redis://127.0.0.1")

@app.task(name='tasks.sendSMS')
def sendSMS(receiver, template_code, params):
    cli = AliyunSMS(access_key_id=config.aliyun_id, access_secret=config.aliyun_key)
    cli.request(phone_numbers=receiver,
                sign='NFLSIO',
                template_code=template_code,
                template_param=params)
    print("SMS: Receiver: " + receiver + ", Template: " + template_code + ", Params: " + json.dumps(params))
