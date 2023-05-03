<template>
	<view>
		<view class='search-container'>
		    <view class='search'>
		      <input type='text' 
		            class='search-input' 
		            placeholder="输入你要找的帖子"         
		            v-model="keywords"/>
		      <view class='search-image' @tap='search'>
		          <image mode="" src="../../static/icon/index-search.png"></image>
		      </view>
		    </view>
		</view>
		<view class="body-list">
			<block v-for="(item, index) in artList" :key="index">
				<view class="body-item">
					<view class="item-header">
						<!-- 头像 -->
						<view class="avatar-item" @tap="openUserInfo" :data-clickrandom="item.random">
							<image class="avatar-border" src="../../static/icon/border.png"></image>
							<image class="user-avatar" :src="item.face"></image>
						</view>
						<view class="nickname-item" >
							<view class="nickname-top">
								<!-- 用户名 -->
								<view class="poster-nickname">{{item.username}}</view>
								<!-- 称号 -->
								<!-- <image :src="" v-if=""></image> -->
							</view>
							<!-- 等级 -->
							<!-- <view class="nickname-bottom">
								<view class="nickname-level">{{level}}</view>
							</view> -->
						</view>
						<view class="icon-item">
							<!-- 删除 -->
							<image src="../../static/icon/index-delete.png"
								   :data-artid="item.id"
								   :data-index="index"
								   @tap="deletePost"></image>
							<!-- 收藏 -->
							<image src="../../static/icon/index-collect.png" v-if="item.follow == 1" @tap="cancelFollow" :data-artid="item.id" :data-index="index"></image>
							<image src="../../static/icon/index-not-collect.png" v-if="item.follow == 0" @tap="follow" :data-artid="item.id" :data-index="index"></image>
						</view>
					</view>
					<!-- 内容 -->
					<view class="item-content" @tap="openInfo" :data-clickartid="item.id">
						<view class="item-content-word">{{item.title}}</view>
						<view class="item-content-image" v-if="item.content.length<2">
							<image mode="aspectFill" :src="item.content[0]"></image>
						</view>
						<view class="item-content-image" v-if="item.content.length>=2">
							<image mode="aspectFill" :src="item.content[0]"></image>
							<image mode="aspectFill" :src="item.content[1]"></image>
						</view>
					</view>
					<view class="item-footer">
						<!-- 时间 -->
						<view class="date">{{item.createtime}}</view>
						<view class="letter-icon">
							<!-- 点赞 -->
							<image @tap="praise" 
								   v-if="item.praise_active==0" 
								   src="../../static/icon/index-topic-zan.png" 
								   :data-artid="item.id"></image>
							<image v-if="item.praise_active==1" 
								   src="../../static/icon/index-like.png"></image>
							<view>{{item.praise}}</view>
						</view>
					</view>
				</view>
			</block>
		</view>
	</view>
</template>
<script>
	var _self, cate = 0, page = 1, Uid, Random, post_follow, error;
	var keywords = 0;
	export default {
		data() {
			return {
				artList:[],
				keywords:''
			}
		},
		onLoad : function(option){
			_self = this;
			page = 1;
			cate = option.Cateid;
			Uid = uni.getStorageSync('SUID');
			Random = uni.getStorageSync('SRAND');
		},
		onShow:function(){
			keywords = 0;
			page = 1;
			this.artList = [];
			this.getNewsList();
			uni.request({
			    url: this.apiServer + 'my&m=getUserInfo',
			    method: 'POST',
			    header: {'content-type' : "application/x-www-form-urlencoded"},
			    data: {
			        random : Random
			    },
			    success: res => {
					console.log(res);
			        if(res.data.status == 'ok'){
						error == 0;
			            post_follow = res.data.data.post_follow;
						if(JSON.stringify(post_follow).charAt(1) !== "[")
						{
							post_follow = [];
						}
						else{
							post_follow = JSON.parse(post_follow);
						}
			        }
					else{
						error = 1;
					}
			    }
			});
			setTimeout(() => {
			   this.updataPraise();
			   this.updataFollow();
			},500);
		},
		onPullDownRefresh : function(){
			keywords = 0;
			page = 1;
		    this.artList = [];
		    this.getNewsList();
			setTimeout(() => {
			   this.updataPraise();
			   this.updataFollow();
			},500);
		},
		onReachBottom : function(){
			this.getNewsList();
			setTimeout(() => {
			   this.updataPraise();
			   this.updataFollow();
			},500);
		},
		methods: {
			search: function(){
				page = 1;
				keywords = this.keywords;
				this.artList = [];
				this.getNewsList();
			},
			updataPraise: function(){
				//判断用户是否点赞帖子
				for(var i = 0; i < _self.artList.length; i++){
					for(var i1 = 0; i1 < _self.artList[i].praise_uid.length; i1++){
					    if(_self.artList[i].praise_uid[i1].praise_uid == Uid){
					         _self.artList[i].praise_active = 1;
							 break;
					    }else{_self.artList[i].praise_active = 0;}	
					}
				}
			},
			updataFollow: function(){
				for(var i = 0; i < _self.artList.length; i++){
					for(var i1 = 0; i1 < post_follow.length; i1++){
					    if(_self.artList[i].id == post_follow[i1]){
					         _self.artList[i].follow= 1;
							 break;
					    }else{_self.artList[i].follow = 0;}	
					}
				}
			},
			getNewsList : function(){
				uni.showLoading({'title':"加载中..."});
				uni.request({
			        url: this.apiServer + 'posts&m=getList&cate='+cate+'&page='+page+'&keywords='+keywords,
			        method: 'GET',
			        success: res => {
						console.log(res);
						
						if(res.data.status == 'empty'){
			                uni.showToast({
			                    title:"已经加载全部帖子",
			                    icon: "none"
			                });
			            }else if(res.data.status == 'ok'){
							var newsList = res.data.data;
							for(var i = 0; i < newsList.length; i++){
			                    // 把图片分离出来
			                    var content = newsList[i].content;
								var praise_uid = newsList[i].praise_uid;
								// 判断内容是否为空
								if(JSON.stringify(praise_uid).charAt(1) !== "[")
								{
									praise_uid = "[{\"praise_uid\":\"0\"}]";
								}
								content = JSON.parse(content);
								praise_uid = JSON.parse(praise_uid);
								
								var imgs = [];
			                    for(var i2 = 0; i2 < content.length; i2++){
			                        if(content[i2].type == 'image'){
			                            imgs.push(content[i2].content);
			                        }
			                    newsList[i].content = imgs;
								newsList[i].praise_uid = praise_uid;
								}
							}
							_self.artList = _self.artList.concat(newsList);
							uni.hideLoading();
							page++;
						}
					},
					complete:function(){
			            uni.stopPullDownRefresh();
						}
				});
			},
			openInfo: function(e){
				var artId = e.currentTarget.dataset.clickartid;
				uni.navigateTo({
					url: '../info/info?artid='+artId
				})
			},
			openUserInfo: function(e){
				var random = e.currentTarget.dataset.clickrandom;
				uni.navigateTo({
					url: '../user_info/user_info?random='+random
				})
			},
			deletePost: function(e){
				var artId = e.currentTarget.dataset.artid;
				var index = e.currentTarget.dataset.index;
				uni.showModal({
				    title:"提示",
				    content:"确定要删除吗?",
				    success:function(e){
						if(e.confirm == true){
							uni.request({
								url: _self.apiServer + 'posts&m=deletePost',
				                method: 'POST',
				                header: {'content-type' : "application/x-www-form-urlencoded"},
								data: {
				                    uid : Uid,
				                    random : Random,
				                    artId : artId
				                },
								success:function(res){
									if(res.data.status == 'ok'){
				                        uni.showToast({title: "已删除", icon:"none"});
				                        _self.artList.splice(index, 1);
				                    }else{
				                        uni.showToast({title: "删除失败", icon:"none"});
				                    }
								}
							});
						}
					},
				});
			},
			cancelFollow: function(e){
				var artid = e.currentTarget.dataset.artid;
				var Index = e.currentTarget.dataset.index;
				uni.request({
					url: _self.apiServer + 'my&m=depostfollow',
				    method: 'POST',
				    header: {'content-type' : "application/x-www-form-urlencoded"},
					data: {
				       postId: artid,
					   random: Random
				    },
					success:function(res){
						console.log(res);
						if(res.data.status == 'ok'){
							_self.artList[Index].follow = 0;
						}
					}
				});
			},
			follow: function(e){
				var artid = e.currentTarget.dataset.artid;
				var Index = e.currentTarget.dataset.index;
				if(error == 1){
					uni.showToast({
					    title: '请先登录',
					    icon: 'none',
					    duration: 2000
					});
				}
				else{
					post_follow.push(artid);
					uni.request({
						url: _self.apiServer + 'my&m=postfollow',
					    method: 'POST',
					    header: {'content-type' : "application/x-www-form-urlencoded"},
						data: {
					       post_follow: JSON.stringify(post_follow),
						   random: Random
					    },
						success:function(res){
							console.log(res);
							_self.artList[Index].follow = 1;
						}
					});
				}
				
			},
			praise: function(e){
				var artId = e.currentTarget.dataset.artid;
				var art_index;
				if(error == 1){
					uni.showToast({
					    title: '请先登录',
					    icon: 'none',
					    duration: 2000
					});
				}
				else{
					this.artList.forEach((value,index,array)=>{
						if(array[index].id==artId){
							art_index=index;
							array[index].praise_uid.push({"praise_uid": Uid});
							}
					});
					_self.artList[art_index].praise++;
					uni.request({
						url: _self.apiServer + 'posts&m=getPraise',
					    method: 'POST',
					    header: {'content-type' : "application/x-www-form-urlencoded"},
						data: {
					        praise: _self.artList[art_index].praise,
							uid: Uid,
							artid: artId,
							praise_uid: JSON.stringify(_self.artList[art_index].praise_uid)
					    },
						success:function(res){
							console.log(res);
							if(res.data.status == 'ok'){
								_self.updataPraise();
					        }
						}
					});
				}
				
			}	
		}
	}
</script>
<style>
.search-container{
		  width: 100%;
		  display: flex;
		  flex-direction: column;
		  justify-content: center;
		  align-items: center;
		}
	
.search-image image{
  width: 35rpx;
  height: 35rpx;
}

.search{
  width: 85%;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-around;
  height: 70rpx;
  background: #f1f1f1;
  border-radius: 30rpx;
}

.search-input{
  width: 240rpx;
  height: 25rpx;
  font-size: 24rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #7d7d7d;
  line-height: 35rpx;
}
.body-list{
  width:100%;
  margin-top: 70rpx;
}

.body-item{
  width:92%;
  background: #ffffff;
  border-radius: 20rpx;
  box-shadow: 0px 0px 20rpx 0px rgba(193,193,193,0.71); 
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-left: 4%;
  margin-bottom: 70rpx;
}

.item-header{
  height: 130rpx;
  width: 92%;
  display: flex;
  flex-direction: row;
}

.avatar-item{
  width: 23%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
}

.avatar-item .user-avatar{
  width: 120rpx;
  height: 120rpx;
  border: 4rpx solid #303030;
  border-radius: 30rpx;
  z-index: 100;
  position: relative;
  top: -30rpx;
}

.avatar-border{
  width: 90rpx;
  height: 90rpx;
  position: absolute;
  z-index: 200;
  left: -22rpx;
  top: -50rpx;
}

.nickname-item{
  width: 61%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.nickname-top{
  width: 100%;
  height: 30%;
  display: flex;
  flex-direction: row;
  align-items: center;
  font-size: 30rpx;
  font-family: PingFangSC, PingFangSC-Medium;
  font-weight: 700;
  text-align: left;
  color: #303030;
  line-height: 40rpx;
}

.poster-nickname{

}

.nickname-top image{
  width: 90rpx;
  height: 27rpx;
  margin-left: 10px;
}

.nickname-bottom{
  width: 100%;
  height: 30%;
  display: flex;
  flex-direction: row;
  align-items: center;
  font-size: 30rpx;
}

.nickname-level{
  width: 90rpx;
  height: 36rpx;
  background: linear-gradient(159deg,#6699CC 11%, #5699CC 94%);
  border-radius: 18rpx;
  box-shadow: 0px 0px 16rpx 0px rgba(30, 167, 247, 0.81); 
  font-size: 20rpx;
  margin-right: 15rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.bottom-name{
  height: 50rpx;
  flex-direction: column;
  align-items: flex-start;
  top: -5rpx;
}

.icon-item{
  width: 16%;
  height: 70%;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.icon-item image{
  width: 42rpx;
  height: 42rpx;
}

.item-content{
  width: 92%;
  margin-bottom: 10rpx;
  background: #ffffff;
  border-radius: 20rpx;
}

.item-content-image{
  width: 100%;
  margin-top: 15rpx;
  display: flex;
  flex-direction: row;
  flex-wrap:wrap;
  justify-content: flex-start;
}

.item-content-image image{
  width: 200rpx;
  height: 200rpx;
  border-radius: 20rpx;
  margin-left: 10rpx;
  margin-bottom: 10rpx;
}

.item-footer{
  width: 92%;
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-bottom: 15rpx;
}

.date{
  width: 70%;
  font-size: 20rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #303030;
}

.letter-icon{
  width: 15%;
  display: flex;
  flex-direction: row;
  font-size: 25rpx;
  font-family: PingFangSC, PingFangSC-Regular;
  font-weight: 400;
  text-align: left;
  color: #303030;
  justify-content: flex-end;
  align-items: center;
}

.letter-icon image{
  width: 50rpx;
  height: 50rpx;
}
</style>