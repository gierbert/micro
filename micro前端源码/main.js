import App from './App'

// #ifndef VUE3
import Vue from 'vue'
Vue.config.productionTip = false;
Vue.prototype.uploadimg = function(img,Url){
	uni.uploadFile({
		url      : Url + 'uploadimg',
		filePath : img,
/* 		header:{"Content-Type": "mutipart/form-data"}, */
		name     : 'file',
		success: (uploadFileRes) => {
			uploadFileRes = JSON.parse(uploadFileRes.data);
			if(uploadFileRes.status != 'ok'){
				uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
				return false;
			}
			//返回图片地址
			 return Url + uploadFileRes.data;
		},
		fail: (e) => {
			console.log(e);
			uni.showToast({title:"上传图片失败,请重试!", icon:"none"});
		}
	})
}
Vue.prototype.checkLogin  = function(backpage, backtype){
	var SUID  = uni.getStorageSync('SUID');
	var SRAND = uni.getStorageSync('SRAND');
	var SNAME = uni.getStorageSync('SNAME');
	var SFACE = uni.getStorageSync('SFACE');
	if(SUID == '' || SRAND == '' || SFACE == ''){
		uni.redirectTo({
			url:"../isregister/isregister?backpage="+backpage+"&backtype="+backtype
		});
		return false;
	}
	return [SUID, SRAND, SNAME, SFACE];
};
var APITOKEN  = 'api2022';
Vue.prototype.apiServer    = 'https://micro.fjl0912.cn/index.php?token='+APITOKEN+'&c=';
Vue.prototype.staticServer = 'https://micro.fjl0912.cn/';

App.mpType = 'app'
const app = new Vue({
    ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif