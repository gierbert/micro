<template>
	<view class="home">
		<!-- 轮播图 -->
		<swiper class="swiper" indicator-dots="true" autoplay="true" interval="3000" duration="1000" circular="true">
			<swiper-item v-for="item in swiperImgs" :key="item.id">
					<img :src="item.img" @tap="click"></image>
			</swiper-item>
		</swiper>
		<!-- 分类导航 -->
		<view class="nav_list">
			<view class="nav_item" v-for="item in nav_img" :key="item.id" @tap="goto" :data-cateid="item.id">
				<image :src="item.icon"></image>
				<text>{{item.name}}</text>
			</view>
		</view>
		<view class="switchSign"></view>
		<view class="navigation" :class="{'navigation_active': topfixed == 1}">
		    <view class="navigation-item">
				<view :data-type="1" @tap="selected" class="select-tab">关注</view>
				<view :class="select==1?'select-tab-bootom':''"></view>
			</view>
		    <view class="navigation-item">
				<view :data-type="2" @tap="selected" class="select-tab">最热</view>
				<view :class="select==2?'select-tab-bootom':''"></view>
			</view>
		    <view class="navigation-item">
				<view :data-type="3" @tap="selected" class="select-tab">收藏</view>
				<view :class="select==3?'select-tab-bootom':''"></view>
			</view>
		</view>
		<view class="body-list" :class="{'top_dis': topfixed == 1}">
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
								<view class="nickname-level" v-if="">{{level}}</view>
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
		<write></write>
	</view>
</template>

<script>
	var _self, page = 1,Uid,Random,post_follow,Type = 1,error,flag=0;
	import write from '../../components/write/write.vue'
	export default {
		components:{
			write
		},
		data() {
			return {
				select: 1,
				artList:[],
				topfixed: 0,
				swiperImgs:[
					{
						img:"../../static/nav_icon/nav1.jpg",
						id:1
					},
					{
						img:"../../static/nav_icon/nav2.jpg",
						id:2
					},
					{
						img:"../../static/nav_icon/nav3.jpg",
						id:3,
					}
				],
				nav_img:[
					{
						id:1,
						name:"联个盟",
						icon:"../../static/nav_icon/alliance.png",
					},
					{
						id:2,
						name:"传声筒",
						icon:"../../static/nav_icon/notice.png",
					},
					{
						id:3,
						name:"学习帮",
						icon:"../../static/nav_icon/study.png",
					},
					{
						id:4,
						name:"帮个忙",
						icon:"../../static/nav_icon/help.png",
					},
					{
						id:5,
						name:"搜soul",
						icon:"../../static/nav_icon/soul.png",
					},
					{
						id:6,
						name:"AI绘画",
						icon:"../../static/nav_icon/aiDraw.png",
					},
					{
						id:7,
						name:"AI问答",
						icon:"../../static/nav_icon/AIchat.png",
					},
					{
						id:8,
						name:"寻宝",
						icon:"../../static/nav_icon/treasure.png",
					}
				]
			}
		},
		onLoad:function(){
			_self = this;
			Uid = uni.getStorageSync('SUID');
			Random = uni.getStorageSync('SRAND');
		},
		onShow:function(){
			page = 1;
			flag = 0;
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
		onReachBottom : function(){
			this.getNewsList();
			setTimeout(() => {
			   this.updataPraise();
			   this.updataFollow();
			},500);
		},
		onPullDownRefresh : function(){
			page = 1;
		    this.artList = [];
		    this.getNewsList();
			setTimeout(() => {
			   this.updataPraise();
			   this.updataFollow();
			},500);
		},
		onPageScroll(res) {
		 			var _this = this;
		 			var temptop;
		 			//uni.createSelectorQuery()返回一个 SelectorQuery 对象实例。
		 			//可以在这个实例上使用 select 等方法选择节点，
		 			const query = uni.createSelectorQuery();
		 			//select在当前页面下选择第一个匹配选择器的节点，
		 			//boundingClientRect添加节点的布局位置的查询请求。其功能类似于 DOM 的 getBoundingClientRect。
		 			query.select('.switchSign').boundingClientRect();
		 			//selectViewport选择显示区域，可用于获取显示区域的尺寸、滚动位置等信息
		 			//scrollOffset添加节点的滚动位置查询请求。
		 			query.selectViewport().scrollOffset();
		 			//exec执行所有的请求。请求结果按请求次序构成数组，在callback的第一个参数中返回。
		 			query.exec(function(res) {
		 				res[0].top // .switchSign节点距离上边界的坐标
		 				res[1].scrollTop // 显示区域的竖直滚动位置
		 				temptop = res[0].top;
		 				if (temptop <= '2') {
		 					_this.topfixed = 1;
		 				} else {
		 					_this.topfixed = 0;
		 				}
		 			})
		 		},
		methods: {
			click:function(){
				console.log(flag);
				if(flag==0){
					uni.showToast({
					    title:"不用点了，啥也没有！",
					    icon: "none"
					});
				}
				flag++;
				if(flag>1&&flag<=5){
					flag++;
					uni.showToast({
					    title:"你再点下试试，有你好看！(｀∀´)Ψ",
					    icon: "none"
					});
				}
				if(flag>5){
					uni.showToast({
					    title:"点吧，使劲点，我不管了！╯^╰",
					    icon: "none"
					});
					flag = -10000;
				}
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
			getNewsList: function(){
				uni.showLoading({'title':"加载中..."});
				uni.request({
				    url: this.apiServer + 'posts&m=getHomePost&page='+page+'&Type='+Type+'&Random='+Random,
				    method: 'GET',
				    success: res => {
						console.log(res);
						if(res.data.status == 'ok'){
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
						else {
						    uni.showToast({
						        title:"已经加载全部帖子",
						        icon: "none"
						    });
						}
					},
					complete:function(){
				        uni.stopPullDownRefresh();
						}
				});
			},
			selected: function(e){
				Type = e.currentTarget.dataset.type;
				page = 1;
				_self.select = Type;
				_self.artList = [];
				_self.getNewsList();
			},
			goto: function(e){
				var Cateid = e.currentTarget.dataset.cateid;
				if(Cateid == 1){
					uni.navigateTo({
						url: '../alliance/alliance?Cateid='+Cateid
					})
				}
				if(Cateid == 2){
					uni.navigateTo({
						url: '../inform/inform?Cateid='+Cateid
					})
				}
				if(Cateid == 3){
					uni.navigateTo({
						url: '../study/study?Cateid='+Cateid
					})
				}
				if(Cateid == 4){
					uni.navigateTo({
						url: '../help/help?Cateid='+Cateid
					})
				}
				if(Cateid == 5){
					uni.navigateTo({
						url: '../soul/soul?Cateid='+Cateid
					})
				}
				if(Cateid == 6){
					uni.navigateTo({
						url: '../aiDraw/aiDraw'
					})
				}
				if(Cateid == 7){
					uni.navigateTo({
						url: '../AIchat/AIchat'
					})
				}
				if(Cateid == 8){
					uni.navigateTo({
						url: '../treasure/treasure'
					})
				}
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
							if(res.data.status == 'ok'){
								_self.artList[Index].follow = 1;
							}
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

<style lang="scss">
	.home{
		swiper{
			width: 100%;
			height: 325rpx;
			img{
				width: 100%;
				height: 100%;
			}
		}
		.nav_list{
			height: 400rpx;
			display: flex;
			flex-wrap: wrap;
			.nav_item{
				width: 20%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				image{
					width: 100rpx;
					height: 100rpx;
				}
				text{
					font-size: 30rpx;
				}
			}
		}
		.navigation{
		  width: 100%;
		  height: 100rpx;
		  background: #ffffff;
		  box-shadow: 0px 0px 10px 0px rgba(203,203,203,0.50); 
		  display: flex;
		  flex-direction: row;
		}
		
		.navigation_active{
			width: 100%;
			padding: 0 25upx;
			position: fixed;
			top: var(--window-top);
			left: 0;
			z-index: 9;
			box-sizing: border-box;
		}
		
		.navigation-item{
		  display: flex;
		  width: 15%;
		  flex-direction: row;
		  align-items: center;
		  justify-content: center;
		}
		
		.select-tab{
		  color: #303030;
		  font-weight: 500;
		  color: #303030;
		  position: absolute;
		}
		
		.select-tab-bootom{
		  width: 60rpx;
		  height: 10rpx;
		  border-radius: 10rpx;
		  box-shadow: 0px 0px 8rpx 0px #6699cc; 
		  background: #6699cc;
		  z-index: 100;
		  position: relative;
		  top: 35rpx;
		}
		
		.top_dis{
			padding-top: 140rpx;
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
		  z-index: 1;
		  position: relative;
		  top: -30rpx;
		}
		
		.avatar-border{
		  width: 90rpx;
		  height: 90rpx;
		  position: absolute;
		  z-index: 2;
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
	}
</style>
