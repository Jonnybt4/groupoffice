go.User = {
	accessToken: localStorage.getItem('accessToken') || sessionStorage.getItem('accessToken'),
	authenticate: function(cb, scope) {
		if(!this.accessToken) {
			return;
		}
		go.Jmap.get(function(data, options, success, response){
			if(data) {
				this.loadSession(data);
			}
			cb.call(scope, data, options, success, response);
		}, this);		
	},
	
	clearAccessToken : function() {
		this.accessToken = null;
		localStorage.removeItem('accessToken');
		sessionStorage.removeItem('accessToken');
	},
  
  loadSession : function(session) {
    this.username = session.username;
    this.apiUrl = session.apiUrl;
    this.downloadUrl = session.downloadUrl;
    this.uploadUrl = session.uploadUrl;
    this.displayName = session.user.displayName;
    this.id = session.user.id;
    this.avatarId = session.user.avatarId;
		this.isAdmin = session.user.isAdmin;

    Ext.apply(GO.settings, session.oldSettings);
  },
  
	isLoggedIn: function() {
		return !Ext.isEmpty(this.username);
	}
};
