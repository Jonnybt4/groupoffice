GO.email.LinkAttachmentDialog = Ext.extend(GO.dialog.LinksDialog,{

	attachmentItem : null, // If this is set to null, then it saves all attachments of the message.
	messagePanel : null,
	
	constructor : function(config){
		
		config = config || {};
		
		Ext.apply(config, {
			title:t("Save the attachment to these items", "email"),
			singleSelect:true,
			filesupport:true
		});

		GO.email.LinkAttachmentDialog.superclass.constructor.call(this,config);
	},
	
	linkItems : function()	{
		
		var selectionModel = this.grid.searchGrid.getSelectionModel();
		var record = selectionModel.getSelected();

		GO.request({
			url:'files/folder/checkModelFolder',
			params:{								
				mustExist:true,
				model:record.data.model_name,
				id:record.data.model_id
			},
			success:function(response, options, result){
				
				if(GO.util.empty(this.attachmentItem)){
					this.saveAllToItem(record, result.files_folder_id);
				} else {
					this.saveToItem(record, result.files_folder_id);
				}
			},
			scope:this
		});
	},
	
	show : function(attachmentItem,messagePanel){
		this.attachmentItem = attachmentItem;
		this.messagePanel = messagePanel;
		GO.email.LinkAttachmentDialog.superclass.show.call(this);
	},

	saveToItem : function(record,files_folder_id){

		if(!GO.files.saveAsDialog){
			GO.files.saveAsDialog = new GO.files.SaveAsDialog();
		}

		GO.files.saveAsDialog.show({
			folder_id : files_folder_id,
			filename: this.attachmentItem.name,
			handler:function(dialog, folder_id, filename){

				GO.request({
					maskEl:dialog.el,
					url: 'email/message/saveAttachment',
					params:{
						uid: this.messagePanel.uid,
						mailbox: this.messagePanel.mailbox,
						number: this.attachmentItem.number,
						encoding: this.attachmentItem.encoding,
						type: this.attachmentItem.type,
						subtype: this.attachmentItem.subtype,
						account_id: this.messagePanel.account_id,
						uuencoded_partnumber: this.attachmentItem.uuencoded_partnumber,
						folder_id: folder_id,
						filename: filename,
						charset:this.attachmentItem.charset,
						sender:this.messagePanel.data.sender,
						tmp_file: this.attachmentItem.tmp_file ? this.attachmentItem.tmp_file : 0,
						filepath:this.messagePanel.data.path//smime message are cached on disk
					},
					success: function(options, response, result)
					{
						dialog.hide();
						this.hide();
					},
					scope:this
				});
			},
			scope:this
		});
	},
	
	saveAllToItem: function(record,files_folder_id){

		GO.request({
			url: 'email/message/saveAllAttachments',
			params:{
				uid: this.messagePanel.uid,
				mailbox: this.messagePanel.mailbox,
				account_id: this.messagePanel.account_id,
				folder_id: files_folder_id
			},
			success: function(options, response, result){
				// Successfully saved all attachments
				this.hide();
			},
			scope:this
		});
	}

});