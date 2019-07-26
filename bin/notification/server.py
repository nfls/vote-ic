from celery import Celery
from celery.schedules import crontab
import requests
from aliyunsms import AliyunSMS
import config
import json
import submail

app = Celery('notification', broker="redis://127.0.0.1", backend="redis://127.0.0.1")

@app.task(name='tasks.sendSMS')
def sendSMS(receiver, template_code, params):
    if config.via == "aliyun":
        if template_code == "login":
            sendAliSMS(receiver, "SMS_171187527", params)
        elif template_code == "confirm":
            sendAliSMS(receiver, "SMS_171192462", params)
    elif config.via == "submail":
        if template_code == "login":
            sendSubmailSMS(receiver, "cZqEf2", params)
        elif template_code == "confirm":
            sendSubmailSMS(receiver, "U4dN43", params)

def sendAliSMS(receiver, template_code, params):
    cli = AliyunSMS(access_key_id=config.aliyun_id, access_secret=config.aliyun_key)
    cli.request(phone_numbers=receiver,
                sign='NFLSIO',
                template_code=template_code,
                template_param=params)
    print("SMS: Receiver: " + receiver + ", Template: " + template_code + ", Params: " + json.dumps(params))
	
def sendSubmailSMS(receiver, template_code, params):
    manager = submail.build("sms")
    msg = manager.message()
    msg['appid'] = config.submail_id
    msg['project'] = template_code
    msg['signature'] = config.submail_key
    msg['to'] = receiver
    msg['vars'] = params
    result = msg.send(stype="xsend", inter=False)
	
    print("SMS: Receiver: " + receiver + ", Template: " + template_code + ", Params: " + json.dumps(params))