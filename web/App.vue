<template>
    <v-app id="inspire">
        <v-app-bar color="indigo" dark fixed app>
            <v-toolbar-title>{{ (vote || {}).title || "南外国际部投票系统" }}</v-toolbar-title>
        </v-app-bar>
        <v-main>
            <v-container fluid fill-height>
                <v-flex>
                    <v-dialog v-model="control.showLoginDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>登录</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                <v-form v-on:submit.prevent>
                                    <v-text-field prepend-icon="person" name="name" label="中文名" v-model="form.name"
                                                  type="text" :error-messages="error.user"></v-text-field>
                                    <v-text-field prepend-icon="phone" name="phone" label="手机号" v-model="form.phone"
                                                  type="text" :error-messages="error.user"></v-text-field>
                                </v-form>
                                <div class="font-weight-bold">
                                    代投、刷票等行为将导致选票作废。<br/>
                                    如有问题请联系 A2-1 周博洋 或 DP1-2 朱孝缘。<br/>
                                    请不要反复发送验证码，否则您的IP将会被封禁。
                                    <br/>
                                </div>
                                您与本站的通讯使用国际标准的加密协议。
                                在投票时，我们会收集关于您设备的信息，用于分析作弊行为。
                                收集的信息将严格保密，不会用于营销或出售给第三方，并将在投票结束后销毁。
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" @click="sendCode" :disabled="loading" style="margin-right: 16px;">发送验证码</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showCodeDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>登录</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                请将前半部分和后半部分一起输入，共6位数。
                                <v-form v-on:submit.prevent>
                                    <v-text-field prepend-icon="lock" name="code" label="验证码" type="text"
                                                  v-model="form.code" :error-messages="error.code"></v-text-field>
                                </v-form>
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <!--<v-btn color="secondary" @click="init" :disabled="loading">取消</v-btn>-->
                                <v-btn color="primary" @click="login" :disabled="loading" style="margin-right: 16px;">登录</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showConfirmDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>提交确认</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                请输入您收到的6位数手机验证码，确认提交投票。
                                <v-form v-on:submit.prevent>
                                    <v-text-field name="code" label="验证码" type="text" v-model="form.confirmCode"
                                                  :error-messages="error.confirm"></v-text-field>
                                </v-form>

                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="secondary" @click="control.showConfirmDialog = false" :disabled="loading">取消</v-btn>
                                <v-btn color="primary" @click="submit" :disabled="loading" style="margin-right: 16px;">提交</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showSuccessDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>投票成功</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                您已投票成功，填写内容已无法修改。您可以凭您短信中的流水号，查询您所提交的投票内容。
                            </v-card-text>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" @click="control.showSuccessDialog = false" style="margin-right: 16px;">好的</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showUnavailableDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>错误</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                您所在的地区无法投票。请尝试关闭任何VPN后再试。
                            </v-card-text>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showAdBlockDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>错误</v-card-title>
                            <v-divider style="margin-bottom: 10px;"></v-divider>
                            <v-card-text>
                                为了保证投票公平，请关闭任何广告拦截插件后再试。
                            </v-card-text>
                        </v-card>
                    </v-dialog>

                    <v-card class="mx-auto" max-width="800" style="margin: 10px" v-if="user != null">
                        <v-card-title class="title" primary-title>欢迎，{{user.name}}</v-card-title>
                        <v-card-text>
                            <div>
                                您的手机号为 <kbd>{{user.phone}}</kbd>，批次标识符为 <kbd>{{user.identifier}}</kbd>。 <br/>
                            </div>
                            <div v-if="mine != null">
                                您的帐号已与 <kbd>{{mine.name}}</kbd> 关联。 <br/>
                            </div><br/>
                            <div class="font-weight-bold">
                                严禁与他人共享账号，分享验证码。代投、刷票等行为将导致您的选票作废。<br/>
                                不操作时，请及时退出。<br/>
                                请先仔细阅读下面的投票说明后，再进行投票。
                            </div>

                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="warning" outlined @click="logout" :disabled="loading">退出</v-btn>
                            <v-btn color="info" outlined @click="query">查票</v-btn>
                            <v-btn color="secondary" outlined @click="init" :disabled="loading" style="margin-right: 8px;">刷新</v-btn>
                        </v-card-actions>
                    </v-card>

                    <v-card class="mx-auto" max-width="800" style="margin: 10px" v-if="vote != null">
                        <v-card-title class="title" primary-title>说明</v-card-title>
                        <v-card-text v-html="content">
                        </v-card-text>
                    </v-card>

                    <v-card class="mx-auto" max-width="800" style="margin: 10px" >
                        <v-card-title class="title" primary-title>投票</v-card-title>
                        <v-card-text>
                            <v-layout>
                                <v-flex>
                                    <v-form v-if="vote != null" ref="form" v-on:submit.prevent>
                                        <v-alert v-if="vote.status === 1" type="error">
                                            现在无法投票。
                                        </v-alert>
                                        <v-alert v-else-if="vote.status === 2" type="info">
                                            投票已开启，请尽快完成投票。
                                        </v-alert>
                                        <v-alert v-else-if="vote.status === 3" type="info">
                                            投票结果已公布，请及时查看。
                                        </v-alert>
                                        <v-alert v-if="voted" type="success">
                                            您已成功提交您的选票。
                                        </v-alert>
                                        <div v-for="section in vote.sections" :key="section.id" style="margin-bottom: 10px;">
                                            <div class="font-weight-bold" style="margin-bottom: -10px;">{{ section.name }}</div>
                                            <v-radio-group v-model="choices[section.id]" :disabled="vote.status !== 2 || voted" required :rules="rules">
                                                <v-radio v-for="choice in section.choices"
                                                         :key="choice.id"
                                                         :value="choice.id"
                                                         :label="choice.name">
                                                </v-radio>
                                            </v-radio-group>
                                        </div>
                                        所有项目必填，不可不选。

                                    </v-form>
                                    <v-alert v-else type="error">
                                        当前暂无投票。
                                    </v-alert>
                                </v-flex>
                            </v-layout>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" @click="sendConfirm" v-if="vote != null && vote.status === 2" :disabled="loading || voted" outlined style="margin-right: 8px;">提交</v-btn>
                        </v-card-actions>
                    </v-card>

                    <v-card class="mx-auto" max-width="800" style="margin: 10px" v-if="vote != null">
                        <v-card-title class="title" primary-title>结果</v-card-title>
                        <v-card-text>
                            <v-alert type="info" v-if="(mine || {}).result != null">
                                您的原始票数为 <kbd>{{ mine.count}}</kbd> 票，附加调整票数为 <kbd>{{ mine.adjust}}</kbd> 票，最终票数为 <kbd>{{ mine.result}}</kbd> 票。
                            </v-alert>
                            <div v-if="results != null">
                                <header>以下是当选名单。有效票数共{{results.count}}张。</header>
                                <v-list-item two-line v-for="item in results.data" :key="item.id">
                                    <v-list-item-content >
                                        <v-list-item-title>{{ item.name }}</v-list-item-title>
                                        <v-list-item-subtitle>{{ item.maxChoice.name}} ({{ item.maxChoice.result}}票）</v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </div>
                            <div v-else>
                                <v-alert type="error">
                                    结果尚未公布。
                                </v-alert>
                            </div>

                        </v-card-text>

                    </v-card>

                    <v-snackbar v-model="control.showSnackBar"> {{error.common}} </v-snackbar>
                </v-flex>
            </v-container>
        </v-main>
        <v-footer class="font-weight-medium">
            <v-flex text-center xs12>
                2020 — <strong>Innovation Club. <a href="https://github.com/nfls/root" style="text-decoration: none;" target="_blank">Source Code</a></strong>
            </v-flex>
        </v-footer>
    </v-app>
</template>

<script>
    export default {
        data: () => ({
            control: {
                showLoginDialog: false,
                showCodeDialog: false,
                showConfirmDialog: false,
                showSnackBar: false,
                showSuccessDialog: false,
                showUnavailableDialog: false,
                showAdBlockDialog: false
            },
            form: {
                name: "",
                phone: "",
                code: "",
                confirmCode: "",
                common: ""
            },
            error: {
                user: "",
                code: "",
                confirm: "",
            },
            valid: true,
            loading: false,
            mine: null,
            results: null,
            vote: null,
            voted: false,
            content: "",
            column: null,
            choices: {},
            user: null,
            rules: [
                v => !!v || '此项必填。'
            ],
        }),
        methods: {
            init() {
                this.form = {
                    name: "",
                    phone: "",
                    code: "",
                    confirmCode: ""
                }
                this.vote = null
                this.choices = {}
                this.results = null
                this.error = {
                    user: "",
                    code: "",
                    confirm: "",
                }
                this.rules = [
                    v => !!v || '此项必填。'
                ]
                this.voted = false
                this.axios.get("/user").then((response) => {
                    this.control.showLoginDialog = false;
                    this.control.showCodeDialog = false;
                    this.load()
                    this.user = response.data["data"]
                }).catch((error) => {
                    if (error.response && error.response.data["code"] === 403) {
                        this.control.showUnavailableDialog = true
                    } else {
                        this.control.showLoginDialog = true;
                        this.control.showCodeDialog = false;
                    }
                })
            },
            sendCode() {
                this.loading = true
                this.axios.post("/send", {
                    "name": this.form.name,
                    "phone": this.form.phone
                }).then((response) => {
                    this.control.showLoginDialog = false
                    this.control.showCodeDialog = true
                    this.loading = false
                }).catch((error) => {
                    if (error.response) {
                        this.error.user = error.response.data["data"]
                    } else {
                        this.error.code = "网络错误，请刷新页面再试。"
                    }
                    this.loading = false
                })
            },
            login() {
                this.loading = true
                this.axios.post("/login", {
                    "name": this.form.name,
                    "phone": this.form.phone,
                    "code": this.form.code
                }).then((response) => {
                    this.loading = false
                    this.control.showLoginDialog = false
                    this.control.showCodeDialog = false
                    this.init()
                }).catch((error) => {
                    if (error.response) {
                        this.error.code = error.response.data["data"]
                    } else {
                        this.error.code = "网络错误，请刷新页面再试。"
                    }
                    this.loading = false
                })
            },
            load() {
                this.loading = true
                this.choices = {}
                this.axios.get("/current").then((response) => {
                    this.vote = response.data["data"]
                    var md = require('markdown-it')()
                    this.content = md.render(this.vote["content"])
                    this.check()
                }).catch((error) => {
                    this.vote = null
                    this.loading = false
                })
            },
            sendConfirm() {
                if (!this.$refs.form.validate()) {
                    this.error.common = "表格尚未完整填写，请检查红色表示的项目。"
                    this.control.showSnackBar = true
                    return
                }
                this.form.confirmCode = ""
                this.loading = true
                this.error.confirm = ""
                this.form.code = ""
                this.axios.post("/send", {
                    phone: this.user.phone,
                    name: this.user.name,
                    confirm: true
                }).then((response) => {
                    this.loading = false
                    this.control.showConfirmDialog = true
                }).catch((error) => {
                    if (error.response) {
                        this.error.common = error.response.data["data"]
                        this.control.showSnackBar = true
                    } else {
                        this.error.common = "网络错误，请刷新页面再试。"
                        this.control.showSnackBar = true
                    }
                    this.loading = false
                })
            },
            submit() {
                let Fingerprint2 = require('@fingerprintjs/fingerprintjs')
                this.loading = true
                Fingerprint2.get((components) => {
                    let results = {}
                    let deviceId = ""
                    for (let i = 0; i < components.length; i++) {
                        results[components[i].key] = components[i].value
                        if (Array.isArray(components[i].value)) {
                            let fg = components[i].value.filter((value) => {
                                return (typeof value === 'string' || value instanceof String) && value.startsWith("canvas fp:data")
                            })
                            if (fg.length > 0)
                                deviceId = fg[0].replace("canvas fp:data:image/png;base64,", "")
                        }
                    }
                    // From stackoverflow
                    var CryptoJS = require("crypto-js");
                    var CryptoJSAesJson = {
                        stringify: function (cipherParams) {
                            var j = {ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)};
                            if (cipherParams.iv) j.iv = cipherParams.iv.toString();
                            if (cipherParams.salt) j.s = cipherParams.salt.toString();
                            return JSON.stringify(j);
                        },
                        parse: function (jsonStr) {
                            var j = JSON.parse(jsonStr);
                            var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Base64.parse(j.ct)});
                            if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
                            if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
                            return cipherParams;
                        }
                    }

                    var AES = require("crypto-js/aes");

                    let data = AES.encrypt(JSON.stringify(
                        {
                            "id": this.vote.id,
                            "deviceId": deviceId,
                            "other": results,
                            "choices": this.choices,
                            "code": this.form.confirmCode
                        }), this.form.confirmCode, {format: CryptoJSAesJson}).toString();

                    this.axios.post("/submit", {
                        "data": data,
                        "code": this.form.confirmCode
                    }).then((response) => {
                        this.control.showConfirmDialog = false
                        this.control.showSuccessDialog = true
                        this.rules = []
                        this.load()
                    }).catch((error)=>{
                        if (error.response) {
                            this.error.confirm = error.response.data["data"]
                        } else {
                            this.error.confirm = "网络错误，请刷新页面再试。"
                        }
                        this.loading = false
                    })
                })
            },
            result() {
                this.axios.get("/result?id="+this.vote.id).then((response)=>{
                    this.results = response.data["data"]
                    this.loading = false
                }).catch((error)=>{
                    this.loading = false
                })
            },
            check(){
                this.axios.get("/voted?id="+this.vote.id).then((response)=>{
                    this.voted = response.data["data"]
                    this.association()
                }).catch((error) => {
                    this.voted = false
                    this.association()
                })
            },
            association() {
                this.axios.get("/mine?id="+this.vote.id).then((response)=>{
                    this.mine = response.data["data"]
                    this.result()
                }).catch((error)=>{
                    this.result()
                })
            },
            logout() {
                this.axios.post("/logout").then((response)=>{
                    this.user = null
                    this.init()
                })
            },
            query() {
                window.open('/query', '_blank')
            }
        },
        mounted() {
            let Fingerprint2 = require('@fingerprintjs/fingerprintjs')
            Fingerprint2.get((components) => {
                let adblock = components.filter((item)=>{
                    return item["key"] === "adBlock"
                })
                if (adblock.length > 0 && adblock[0]["value"]) {
                    this.control.showAdBlockDialog = true
                } else {
                    this.init()
                }
            })
        },
        props: {
            source: String
        }
    }
</script>

<style>
    v-card-text {

    }
</style>