
$(".payKuan").css("display", "none");
// 实例化vue对象
new Vue({
	el: ".main",
	data: {
		// 套餐选择
		selectMeal: list,
		// 选择套餐内容变量
		displayMeal: list[0].money, //默认为第一个套餐的金额
		// 用户相关信息
		userInfo: info
	},
	mounted: function() {
		// 默认选中第一个套餐
		$(".am-thumbnail>p").eq(0).addClass("selectedMeal");
	},
	methods: {
		// 点击叉叉支付面板消失
		displayNone() {
			$(".payKuan").css("display", "none");
		},
		// 立即购买
		quikBuys() {
			$(".payKuan").css("display", "block");
		},
		// 立即付款
		payQuikly() {
			var _this = this;
			// 提交后台参数
			/* 
				pay	支付方式
				setMealId	套餐ID	
				price	套餐金额
				flow	套餐容量(多少天)
				num	套餐数量		
				uid	用户ID	
				sid	经销商ID		
				tid	设备类型ID 
			*/
			var mealInfo = {
				pay: $(".icon-circleyuanquan").parent().attr("type"),
				setMealId: $(".selectedMeal").attr("mealId"),
				price: $(".selectedMeal").attr("mealMoney"),
				flow: $(".selectedMeal").attr("flow"),
				num: 1,
				uid: _this.userInfo.uid,
				sid: _this.userInfo.vid,
				tid: $(".selectedMeal").attr("tId")
			};
			console.log(mealInfo);
			// 提交到后台路径
			var mealUrl = getURL("Home", "Pay/setmealbuy");
			$.ajax({
				url: mealUrl,
				type: "post",
				data: mealInfo,
				success: function(res) {
					console.log("第一步", res)
					if(res.status == 200) {
						var isWx = isWX();
						if(isWx) {
							// 调用微信支付接口
							weixinJog(res);
						}else {
							noticeFn({text: "非微信环境下"});
						}
					}else {
						noticeFn({text: "支付失败!请重试"});
					}
				},
				error: function(res) {
					noticeFn({text: '系统出了一点小问题，请稍后再试！'});
					console.log("失败", res);
				}
			});
			// 微信支付接口
			function weixinJog(res) {
				/*
					openId	用户OpenID		
					money	价格			
					order_id	订单号			
					content	订单内容		
					notify_url	订单回调地址 
				*/
				var jogData = {
					openId: open_id,
					money: res.price,
					order_id: res.order_id,
					content: res.title,
					notify_url: res.notify_url
				}
				var jogUrl = getURL("Home", "Pay/wxres");
				$.ajax({
					url: jogUrl,
					type: "post",
					data: jogData,
					success: function(res) {
						console.log("成功", res);
						if(res.status != 201) {
							// 调用微信支付
							weixinPay(res);
						}else {
							noticeFn({text: "付款出错!请重新支付"});
						}
						
					},
					error: function(res) {
						console.log("失败", res);
						noticeFn({text: '系统出了一点小问题，请稍后再试！'});
					}
				})
			}
			// 微信支付方法
			function weixinPay(res){
				var type = Object.prototype.toString.call(res);
				if(type === '[object Object]'){
					res = JSON.stringify(res);
				}
				WeixinJSBridge.invoke(
					'getBrandWCPayRequest',
					JSON.parse(res),
					function(res){
						if (res.err_msg.substr(-2) == 'ok') {
							// 付款成功，跳转前台主页
							noticeFn({text: "付款成功"});
							location.href = "{{:U('Home/Pay/paySuccCom')}}" + "?index";
						} else if (res.err_msg.substr(-6) == 'cancel') {
							// 取消付款
						}else{
							// 付款失败
							// 跳转到待付款订单页面
							noticeFn({text: "付款失败"});
							location.href = "{{:U('Home/Pay/payFailed')}}" + "?index";
						}
					}
				);
			};
		},
		// 套餐选择
		selectMeals(ev) {
			var _this = this;
			var target = ev.target || ev.srcElement;
			if(target.nodeName.toLowerCase() == "p") {
				$(target).addClass("selectedMeal").parent().siblings().children().removeClass("selectedMeal");

				_this.displayMeal = parseInt($(target).attr("mealMoney"));
				console.log($(target).attr("mealMoney"))
			}
		},
	}
})