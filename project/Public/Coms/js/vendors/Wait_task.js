var wait_task = new Vue({
	el:"#wait_task_vue",
	data:{
		type:"",
		// 待办任务页面
		task:[ 
			{
				task_text:"待安装",
				task_number:tone
				,
				task_id:"task_one"
			},
			{
				task_text:"待维修",
				task_number:ttwo,
				task_id:"task_two"
			},
			{
				task_text:"待维护",
				task_number:tf,
				task_id:"task_three"
			},
		],
		//待办任务列表
		sevice_list:[],
		// 任务详情页面
		service_details_info:{},
		// 派工信息页面
		plan_personnel_info_bg:{
			new_work_order:"",
			install_personnel_info:[] 
		},
		search:"",//搜索
		// 请求ajax
		// getAjax: "",
		// data:"",
		// url:""
   
	},   
	methods:{
		// 跳转页面改变url（公共）
		// url_public:function(num){
		// 	var url = window.document.location.href.toString();
		// 	var href = url.split("?")[0];
		// 	location.href = href+"?index="+num;
		// },
		url_public:function(key,value){
			var url = window.document.location.href.toString();
			var href = url.split("?")[0];
			location.href = href+"?"+key+"="+value;
		},
		// 在待办任务（首页）
		task_one:function(type){
			var _this = this;
			// title
			var data_info = _this.task[type].task_text;
			localStorage.setItem("title",data_info);
			wait_task.url_public("type",type);// 页面跳转
		},
		//搜索用户页面（第二页）
		service_details:function(index){
			var _this = this;
			var id = _this.sevice_list[index].id;
			wait_task.url_public("id",id);// 页面跳转
		},
        // 点击搜索小图标提交表单
        subClick:function(){
        	console.log(this.search)
        	$.ajax({
                url: '',
                data: {searchword: this.search},
                type: "post",
                success: function(res) {
                    console.log('res: ',res);
                    if(res.code == 200){
                        wait_task.service_details_info = res.data;
                    }else{
                        wait_task.service_details_info = [{
                            name: '&emsp;',
                            phone: '查无数据',
                            device_code: '&emsp;'            
                        }];
                    }
                },
                error: function(err) {
                    wait_task.service_details_info = [{
                        name: '&emsp;',
                        phone: '查无数据',
                        device_code: '&emsp;'            
                    }];
                }
            })
        },
		// 派工按钮  服务详情页面（第三页）
		plan_personnel_inp:function(no){
			var _this = this;
			wait_task.url_public("no",no);
			$("#service_details_bg").hide();
			$("#plan_personnel_info_bg").show();
		},
		// 点击派工信息页面中的选择，弹出蒙版
		select_masking:function(){
			// 弹出蒙版
			var  _this = this;
			$("#plan_personnel_mask_bg").show();
		},
		// 选中安装人员
		pitch_on:function(index_personnel,even){
			var $this = this;
			// 获取当前点击的元素标签
			var el = event.target;
			var $el = $(el);
			console.log($el.attr("phone"))
			$el.css({"fontSize":"0.64rem","color":"#1a1a1a"}).siblings().css({"fontSize":"0.512rem","color":"#b3b3b3"});
			$("#select_personnel").html($el.html());//安装人员名字
			$("#select_cell").html($el.attr("phone"));//安装人员电话
			$("#plan_personnel_mask_bg").hide();
			$("#plan_personnel_submit").css({"background":"#0d94f3"});
		},
		// 提交按钮
		plan_personnel_submit:function(){
			var _this = this;
			if($("#select_personnel").html() == "选择" && $("#select_cell").html() == ""){
				noticeFn({text: '请选择选择安装人员,匹配联系方式',time: '1500'});
				return;
			}
			var url = getURL("Coms","Vendors/add_per");
			var id = {
				id:JSON.parse(sessionStorage.getItem("id"))
			}
			postPub(function(res){
				console.log(res);
				noticeFn({text: res.text,time: '1500'});
				// setTimeout(function(){
				// 	location.href = '{{:U("Coms/Vendors/wait_task")}}';
				// },500);
			},url,id);
		},
	},
	//实例创建前
	created:function(){
		var url,data,key,value;
		var _this = this;
		var href = location.href.split("?")[1];
		// 待办任务详情
		if(href != undefined){
			key = href.split("=")[0];
			value = href.split("=")[1];
			if(key == "type"){
				console.log(key);
				// 0-安装 1-维修 2-维护
				url = getURL("Coms", "Vendors/sevice_list");
				data = {
					type:value
				}
			 	postPub(function(res){
			 		var task_user = {};
			 		// 成功返回数据
			 		if(res.msg == 0){
			 				console.log(res.res)
				 			_this.sevice_list = res.res;
			 		}else if(res.msg == 1){
			 			// 数据返回失败
			 			_this.sevice_list = [];
			 			_this.sevice_list = [{
		 					name:" ",
		 					phone:"暂无数据",
		 					addtime:""
		 				}];
			 		}else if(res.msg == 2){
			 			// 返回数据失败
			 			noticeFn({text: res.text});
			 		}
			 	},url,data);
			}else if(key == "id"){
				// 服务详情
				url = getURL("Coms","Vendors/details");
				sessionStorage.setItem("id",value);
				data = {id:value};
				postPub(function(res){
					if(res.msg == 0){
						// 数据返回成功
						console.log("成功",res.res);
						if(res.res != ""){
							_this.service_details_info = res.res;
						}
					}else{
						console.log("失败",res.res);
						// 数据返回失败
			 			noticeFn({text: res.text});
			 		}
				},url,data);
			}else if(key == "no"){
				// 派工
				_this.plan_personnel_info_bg.new_work_order = value;
				url = getURL("Coms","Vendors/per");
				postPub(function(res){
					// console.log("成功",res);
					if(res.msg == 0){
						// 数据返回成功
						console.log(res.res)
						for(var i = 0;i<res.res.length;i++){
							_this.plan_personnel_info_bg.install_personnel_info = [];//清空
							_this.plan_personnel_info_bg.install_personnel_info.push(res.res[i]);					}
					}
				},url);
			}
		};
	},
	mounted:function(){
		// var _this = this;
		// _this.getAjax = function(callback,url,data){
		// 	$.ajax({
		// 		url:url,
		// 		type:"post",
		// 		data:_this.data,
		// 		success:function(res){
		// 			// console.log("成功",res)
		// 			if(res.code == 200){
		// 				callback({res:res.data,msg:0});
		// 			}else{
		// 				callback({res:"",text:"系统出错，请稍候再试",msg:1});
		// 			}
		// 		},
		// 		error:function(res){
		// 			console.log("失败",res);
		// 			callback({res: "", text: "系统出错，请稍后再试!", msg:1});
		// 		}
		// 	})
		// }
		// 待办任务列表
		// var sevice_list = JSON.parse(sessionStorage.getItem("sevice_list"));
		// if(sevice_list){
		// 	for(var i = 0;i<sevice_list.length;i++){				sevice_list[i].addtime = getLocalTime(sevice_list[i].addtime);
		// 		_this.sevice_list.push(sevice_list[i])			}
		// }
		// // 任务详情页面
		// var detail = JSON.parse(sessionStorage.getItem("service_details_info"));
		// if(detail){
		// 	_this.service_details_info = detail;
		// 	_this.plan_personnel_info_bg.new_work_order = detail.no;
		// }
	}
})
