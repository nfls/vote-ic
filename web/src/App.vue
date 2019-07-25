<template>
    <v-app id="inspire">
        <v-app-bar color="indigo" dark fixed app>
            <v-toolbar-title>{{ (vote || {}).title || "南外国际部投票系统" }}</v-toolbar-title>
        </v-app-bar>
        <v-content>
            <v-container fluid fill-height>
                <v-flex>
                    <v-dialog v-model="control.showLoginDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>登录</v-card-title>

                            <v-card-text>
                                <v-form>
                                    <v-text-field prepend-icon="person" name="name" label="中文名" v-model="name"
                                                  type="text" :error-messages="usererror"></v-text-field>
                                    <v-text-field prepend-icon="phone" name="phone" label="手机号" v-model="phone"
                                                  type="text" :error-messages="usererror"></v-text-field>
                                </v-form>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" flat @click="sendCode" :disable="loading">发送动态码</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showCodeDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>登录</v-card-title>

                            <v-card-text>
                                <v-form>
                                    <v-text-field prepend-icon="lock" name="code" label="动态码" type="text"
                                                  v-model="code" :error-messages="codeerror"></v-text-field>
                                </v-form>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" flat @click="login" :disable="loading">登录</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>

                    <v-dialog v-model="control.showConfirmDialog" :persistent="true" width="500">
                        <v-card>
                            <v-card-title>提交确认</v-card-title>

                            <v-card-text>
                                请输入您收到的手机验证码，确认提交投票。
                                <v-form>
                                    <v-text-field name="code" label="手机验证码" type="text" v-model="form.confirmCode"
                                                  :error-messages="error.confirm"></v-text-field>
                                </v-form>
                            </v-card-text>

                            <v-divider></v-divider>

                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="secondary" flat @click="control.showConfirmDialog = false">取消</v-btn>
                                <v-btn color="primary" flat @click="submit" :disable="loading">提交</v-btn>
                            </v-card-actions>
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
                            </div>
                            <div class="font-weight-bold">
                                严禁与他人共享账号，分享验证码。代投、刷票等行为将导致您的选票作废。<br/>
                                请先仔细阅读下面的投票说明后，再进行投票。
                            </div>

                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="warning" @click="logout">退出</v-btn>
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
                                    <v-form v-if="vote != null">
                                        <v-alert v-if="vote.status !== 2" type="error">
                                            现在暂时无法投票。
                                        </v-alert>
                                        <div v-for="section in vote.sections" :key="section.id">
                                            <header> {{ section.name }}</header>
                                            <v-radio-group v-model="choices[section.id]" row :disabled="vote.status !== 2">
                                                <v-radio v-for="choice in section.choices"
                                                         :key="choice.id"
                                                         :value="choice.id"
                                                         :label="choice.name">
                                                </v-radio>
                                            </v-radio-group>
                                        </div>

                                    </v-form>
                                    <v-alert v-else type="error">
                                        当前暂无投票。
                                    </v-alert>

                                </v-flex>
                            </v-layout>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" @click="sendConfirm" :disabled="vote == null || vote.status !== 2">提交</v-btn>
                        </v-card-actions>
                    </v-card>

                    <v-card class="mx-auto" max-width="800" style="margin: 10px" v-if="vote != null">
                        <v-card-title class="title" primary-title>结果</v-card-title>
                        <v-card-text>
                            <v-alert type="info" v-if="(mine || {}).result != null">
                                您的原始票数为 <kbd>{{ mine.count}}</kbd> 票，改选委调整票数为 <kbd>{{ mine.adjust}}</kbd> 票，最终票数为 <kbd>{{ mine.result}}</kbd> 票。
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
                </v-flex>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    export default {
        data: () => ({
            control: {
                showLoginDialog: false,
                showCodeDialog: false,
                showConfirmDialog: false
            },
            form: {
                confirmCode: ""
            },
            error: {
                confirm: ""
            },
            loading: false,
            mine: null,
            results: null,
            vote: null,
            content: "",
            column: null,
            choices: {},
            name: "",
            phone: "",
            code: "",
            usererror: "",
            codeerror: "",
            user: null
        }),
        methods: {
            init() {
                this.axios.get("/user").then((response) => {
                    this.load()
                    this.user = response.data["data"]
                }).catch((err) => {
                    this.dialogMobile = true;
                })
            },
            sendCode() {
                this.loading = true
                this.axios.post("/send", {
                    "name": this.name,
                    "phone": this.phone
                }).then((response) => {
                    this.control.showLoginDialog = false
                    this.control.showCodeDialog = true
                    this.loading = false
                }).catch((error) => {
                    if (error.response) {
                        this.usererror = error.response.data["data"]
                    }
                    this.loading = false
                })
            },
            login() {
                this.axios.post("/login", {
                    "name": this.name,
                    "phone": this.phone,
                    "code": this.code
                }).then((response) => {
                    this.control.showLoginDialog = false
                    this.control.showCodeDialog = false
                    this.init()
                }).catch((error) => {
                    if (error.response) {
                        this.codeerror = error.response.data["data"]
                    }
                })
            },
            load() {
                this.axios.get("/current").then((response) => {
                    this.vote = response.data["data"]
                    var md = require('markdown-it')()
                    this.content = md.render(this.vote["content"])
                    this.association()
                })
            },
            sendConfirm() {
                this.axios.post("/send", {
                    phone: this.user.phone,
                    name: this.user.name,
                    confirm: true
                }).then((response) => {
                    this.control.showConfirmDialog = true
                })
            },
            submit() {
                let Fingerprint2 = require('fingerprintjs2')
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
                    this.axios.post("/submit", {
                        "id": this.vote.id,
                        "deviceId": deviceId,
                        "other": results,
                        "choices": this.choices
                    }).then((response) => {

                    }).catch((error)=>{
                        if (error.response) {
                            this.error.confirm = error.response.data["data"]
                        }
                    })
                })
            },
            result() {
                this.axios.get("/result?id="+this.vote.id).then((response)=>{
                    this.results = response.data["data"]
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

            }
        },
        mounted() {
            this.init()
        },
        props: {
            source: String
        }
    }
</script>