#!/usr/bin/python3

import requests
import json
import time
import pytz
import datetime
import optparse

def login(session, username, password):
    headers = {
        'Connection': 'keep-alive',
        'Pragma': 'no-cache',
        'Cache-Control': 'no-cache',
        'Accept': 'application/json, text/javascript, */*; q=0.01',
        'X-Requested-With': 'XMLHttpRequest',
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.89 Safari/537.36 Edg/84.0.522.40',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Origin': 'https://xxcapp.xidian.edu.cn',
        'Sec-Fetch-Site': 'same-origin',
        'Sec-Fetch-Mode': 'cors',
        'Sec-Fetch-Dest': 'empty',
        'Referer': 'https://xxcapp.xidian.edu.cn/uc/wap/login',
        'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
    }
    data = {
        'username': username,
        'password': password
    }
    session.post('https://xxcapp.xidian.edu.cn/uc/wap/login/check',
                 headers=headers, data=data)

def submit(session):
    headers = {
        "Accept": "application/json, text/plain, */*",
        "Accept-Encoding": "gzip, deflate, br",
        "Accept-Language": "zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6",
        "User-Agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) "
        "Version/13.0.3 Mobile/15E148 Safari/604.1 Edg/84.0.4147.89",
        "X-Requested-With": "XMLHttpRequest",
        "Referer": "https://xxcapp.xidian.edu.cn/site/ncov/xisudailyup",
        "Origin": "https://xxcapp.xidian.edu.cn"
    }
    data = {
        "sfzx": "1",  # 是否在校(0->否, 1->是)
        "tw": "1",
        # 体温 (36℃->0, 36℃到36.5℃->1, 36.5℃到36.9℃->2, 36.9℃到37℃.3->3, 37.3℃到38℃->4, 38℃到38.5℃->5, 38.5℃到39℃->6, 39℃到40℃->7,
        # 40℃以上->8)
        "sfcyglq": "0",  # 是否处于隔离期? (0->否, 1->是)
        "sfyzz": "0",  # 是否出现乏力、干咳、呼吸困难等症状？ (0->否, 1->是)
        "qtqk": "",  # 其他情况 (文本)
        "askforleave": "0",  # 是否请假外出? (0->否, 1->是)
        "geo_api_info": "{\"type\":\"complete\",\"position\":{\"Q\":34.121994628907,\"R\":108.83715983073,"
        "\"lng\":108.83716,\"lat\":34.121995},\"location_type\":\"html5\",\"message\":\"Get ipLocation "
        "failed.Get geolocation success.Convert Success.Get address success.\",\"accuracy\":65,"
        "\"isConverted\":true,\"status\":1,\"addressComponent\":{\"citycode\":\"029\","
        "\"adcode\":\"610116\",\"businessAreas\":[],\"neighborhoodType\":\"\",\"neighborhood\":\"\","
        "\"building\":\"\",\"buildingType\":\"\",\"street\":\"雷甘路\",\"streetNumber\":\"264号\","
        "\"country\":\"中国\",\"province\":\"陕西省\",\"city\":\"西安市\",\"district\":\"长安区\","
        "\"township\":\"兴隆街道\"},\"formattedAddress\":\"陕西省西安市长安区兴隆街道西安电子科技大学长安校区办公辅楼\",\"roads\":[],"
        "\"crosses\":[],\"pois\":[],\"info\":\"SUCCESS\"}",
        "area": "陕西省 西安市 长安区",  # 地区
        "city": "西安市",  # 城市
        "province": "陕西省",  # 省份
        "address": "陕西省西安市长安区兴隆街道西安电子科技大学长安校区行政辅楼",  # 实际地址
    }
    response = session.post('https://xxcapp.xidian.edu.cn/xisuncov/wap/open-report/save',headers=headers, data=data)
    return json.loads(response.text)['m']
    
def check_in(student_id, password):
    session = requests.session()
    login(session, student_id, password)
    return submit(session)

parse = optparse.OptionParser("Param: --id <id> --pw <pass>")
parse.add_option("--id", dest="id", help="account id")
parse.add_option("--pw", dest="pw", help="account password")
(options, args) = parse.parse_args()
print(check_in(options.id,options.pw))