/* ODE PLUGIN JS */

ODE = {};

ODE.init = function()
{
    //console.log('ODE PLUGIN INITIALIZED');

    var ta = $('.ow_comments_input textarea');
    $.each(ta, function(idx, obj) {
        if ( $(obj).attr('data-preview-added') ) {
            return;
        } else {
            $(obj).attr('data-preview-added', true);
        }
        var id = obj.id;
        var newEl = $(obj).parent().find('.ow_attachments').first().prepend($('<a href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'ic_lens.svg) no-repeat center;" data-id="' + id + '"></a>'));
        newEl = newEl.children().first();
        newEl.click(function (e) {
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {text:'testo'} , {width:'90%', height:'60vh', iconClass:'ow_ic_lens', title:''});
        });
    });

    /*var ds_params ={
        component   : "data-sevc-controllet",
        params      :{
            'data-url'          : "http://dati.lazio.it/catalog/api/action/datastore_search?resource_id=722b6cbd-28d3-4151-ac50-9c4261298168&limit=500",
            'deep-url'          : "http://service.routetopa.eu/WebComponentsDEV/DEEP/",
            'datalets-list-url' : "http://service.routetopa.eu/WebComponentsDEV/DEEP/datalets-list"
        },
        fields     : Array(),
        placeHolder : "ode_controllet_placeholder"
    };

    //TODO Export deep_url
    ComponentService.deep_url = 'http://service.routetopa.eu/WebComponentsDEV/DEEP/';
    ComponentService.getComponent(ds_params);*/

    ComponentService.deep_url = ODE.deep_url;

    // Listen for datalet event
    window.addEventListener('data-sevc-controllet.dataletCreated', function (e) {
       ODE.setDataletValues(e.detail.data);

        var data = e.detail.data;
        $('#ode_controllet_placeholder').slideToggle('fast');
        ODE.loadDatalet(data.datalet, data.dataUrl, '', data.fields, 'ode_controllet_placeholder');

    });

};

ODE.setDataletValues = function (data)
{
    $('input[name=ode_datalet]').val(data.datalet);
    $('input[name=ode_dataset]').val(data.dataUrl);
    $('input[name=ode_query]').val('"'+data.fields.join('","')+'"');
    $('input[name=ode_forder]').val('');

    ODE.dataletParameters.component = data.datalet;
    ODE.dataletParameters.dataset   = data.dataUrl;
    ODE.dataletParameters.forder    = '';
    ODE.dataletParameters.query     = '"'+data.fields.join('","')+'"';

    previewFloatBox.close();
};

ODE.loadDatalet = function(component, dataset, forder, query, placeholder)
{
    ComponentService.getComponent({
        component   : component,
        params      : {'data-url' : dataset, 'fields-order' : forder},
        fields      : query,
        placeHolder : placeholder
    });
};

ODE.odeLoadNewItem = function(params, preloader, id, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    if ( typeof preloader == 'undefined' )
    {
        preloader = true;
    }

    if (preloader)
    {
        var $ph = self.getPlaceholder();
        this.$listNode.prepend($ph);
    }
    this.loadItemMarkup(id, params, function($a) {
        this.$listNode.prepend($a.hide());

        if ( callback )
        {
            callback.apply(self);
        }

        self.adjust();
        if ( preloader )
        {
            var h = $a.height();
            $a.height($ph.height());
            $ph.replaceWith($a.css('opacity', '0.1').show());
            $a.animate({opacity: 1, height: h}, 'fast');
        }
        else
        {
            $a.animate({opacity: 'show', height: 'show'}, 'fast');
        }
    });

};

ODE.loadItemMarkup = function(id, params, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    params.feedData = self.data;
    params.cycle = params.cycle || {lastItem: false};

    params = JSON.stringify(params);

    NEWSFEED_Ajax(window.ODE.ajax_load_item, {p: params}, function( markup ) {

        if ( markup.result == 'error' )
        {
            return false;
        }

        var $m = $(markup.html);
        callback.apply(self, [$m]);
        OW.bindAutoClicks($m);

        self.processMarkup(markup);
    });
};

ODE.dataletParameters =
{
    component:'',
    dataset:'',
    forder:'',
    query:''
};

ODE.commentSendMessage = function(message, context)
{
    var self = context;
    var dataToSend = {
        entityType: self.entityType,
        entityId: self.entityId,
        displayType: self.displayType,
        pluginKey: self.pluginKey,
        ownerId: self.ownerId,
        cid: self.uid,
        attchUid: self.attchUid,
        commentCountOnPage: self.commentCountOnPage,
        commentText: message,
        initialCount: self.initialCount,
        datalet: ODE.dataletParameters
    };

    if( self.attachmentInfo ){
        dataToSend.attachmentInfo = JSON.stringify(self.attachmentInfo);
    }
    else if( self.oembedInfo ){
        dataToSend.oembedInfo = JSON.stringify(self.oembedInfo);
    }

    $.ajax({
        type: 'post',
        //url: self.addUrl,
        url: ODE.ajax_add_comment,
        data: dataToSend,
        dataType: 'JSON',
        success: function(data){
            self.repaintCommentsList(data);
            OW.trigger('base.photo_attachment_uid_update', {uid:self.attchUid, newUid:data.newAttachUid});
            self.eventParams.commentCount = data.commentCount;
            OW.trigger('base.comment_added', self.eventParams);
            self.attchUid = data.newAttachUid;

            self.$formWrapper.removeClass('ow_preloader');
            self.$commentsInputCont.show();
        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){

        }
    });

    self.$textarea.val('').keyup().trigger('input.autosize');
};

OwComments.prototype.initTextarea = function()
  {
    /* ODE */
    $('#ode_controllet_placeholder').hide();
    /* ODE */

    var self = this;
    this.realSubmitHandler = function(){

        self.initialCount++;

        //self.sendMessage(self.$textarea.val());
        ODE.commentSendMessage(self.$textarea.val(), self);

        self.attachmentInfo = false;
        self.oembedInfo = false;
        self.$hiddenBtnCont.hide();
        if( this.mediaAllowed ){
            OWLinkObserver.getObserver(self.textAreaId).resetObserver();
        }
        self.$attchCont.empty();
        OW.trigger('base.photo_attachment_reset', {pluginKey:self.pluginKey, uid:self.attchUid});
        OW.trigger('base.comment_add', self.eventParams);

        self.$formWrapper.addClass('ow_preloader');
        self.$commentsInputCont.hide();

    };

    this.submitHandler = this.realSubmitHandler;

    this.$textarea
        .bind('keypress',
        function(e){
            if( e.which === 13 && !e.shiftKey ){
                e.stopImmediatePropagation();
                var textBody = $(this).val();

                if ( $.trim(textBody) == '' && !self.attachmentInfo && !self.oembedInfo ){
                    OW.error(self.labels.emptyCommentMsg);
                    return false;
                }

                self.submitHandler();
                return false;
            }
        }
    )
        .one('focus', function(){$(this).removeClass('invitation').val('').autosize({callback:function(data){OW.trigger('base.comment_textarea_resize', self.eventParams);}});});

    this.$hiddenBtnCont.unbind('click').click(function(){self.submitHandler();});

    if( this.mediaAllowed ){
        OWLinkObserver.observeInput(this.textAreaId, function( link ){
            if( !self.attachmentInfo ){
                self.$attchCont.html('<div class="ow_preloader" style="height: 30px;"></div>');
                this.requestResult( function( r ){
                    self.$attchCont.html(r);
                    self.$hiddenBtnCont.show();

                    OW.trigger('base.comment_attach_media', {})
                });
                this.onResult = function( r ){
                    self.oembedInfo = r;
                    if( $.isEmptyObject(r) ){
                        self.$hiddenBtnCont.hide();
                    }
                };
            }
        });
    }
};