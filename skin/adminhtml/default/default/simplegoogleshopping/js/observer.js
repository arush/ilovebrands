document.observe('click',function(e){
		
    if(e.findElement('input[type=checkbox]')){ 
        i=e.findElement('input[type=checkbox]');
		
        i.ancestors().each(function(a){
            if(a.hasClassName('fieldset')) 	selector=$(a.id);
        })
        if(selector.id=='attributes-selector'){
            if(i.checked==true)	i.ancestors()[1].select('div')[0].select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){
                h.disabled=false
            })
            else i.ancestors()[1].select('div')[0].select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){
                h.disabled=true
            })
        }
			
        i.ancestors()[1].select('li').each(function(li){
            if(i.checked==true) {
                li.select('INPUT')[0].checked=true;
            }
            else {
                li.select('INPUT')[0].checked=false;
            }
        })

		
		
        setValues(selector);
		
		
        selector.select('.selected').each(function(s){
            s.removeClassName('selected')
        })
        selector.select('.node').each(function(li){
            if(li.select('INPUT')[0].checked==true){
                li.addClassName('selected');
				
            }
        }) 
    }
    
})
document.observe('dom:loaded', function(){


    

    $$('.mapping').each(function(m){
        m.observe('focus',function(e){
            if(m.value.trim()==dfm.mappingStr){
                m.value='';
                m.setStyle({
                    color:'green'
                })
				
            }
            setValues($('category-selector'));
        })
        m.observe('blur',function(e){
            if(m.value.trim()=='' || m.value.trim()==dfm.mappingStr){
                m.value=dfm.mappingStr;
                m.setStyle({
                    color:'grey'
                })
				
            }
            setValues($('category-selector'));
        })
        m.observe('keydown',function(e){
           
            switch(e.keyCode){
              
                case 45:
                    mapper= e.findElement('.mapping');
                    if($$('.mapping').indexOf(mapper)+1<$$('.mapping').length){
                        $$('.mapping')[($$('.mapping').indexOf(mapper)+1)].focus();
                        $$('.mapping')[($$('.mapping').indexOf(mapper)+1)].value=mapper.value;
                    }
                    break;
                case 35:
                    mapper= e.findElement('.mapping');
                    mapper.up().up().select('ul').each(function(u){
                        u.addClassName('open')
                    })
                    mapper.up().up().select('input[type=text]').each(function(i){
                        i.focus();
                        i.value=mapper.value;
                    })
                    break;
            } 
        })
    })

    if($('simplegoogleshopping_categories').value!="*" && $('simplegoogleshopping_categories').value!=""){
        attributes=$('simplegoogleshopping_categories').value.evalJSON();
        attributes.each(function(attribute){
            if($('category_'+attribute.line)){
                if(attribute.checked){
                    $('category_'+attribute.line).checked=true;
                    $('category_'+attribute.line).ancestors()[1].addClassName('selected');
                    if($('category_'+attribute.line).ancestors()[2].previous())
                        $('category_'+attribute.line).ancestors()[2].previous().select('.tree_view')[0].addClassName('open');
                }
                if(attribute.mapping!=""){
                    $('category_'+attribute.line).next().next().next().value=attribute.mapping;
                    $('category_'+attribute.line).next().next().next().setStyle({
                        color:'green'
                    })
                    if($('category_'+attribute.line).ancestors()[2].previous())
                        $('category_'+attribute.line).ancestors()[2].previous().select('.tree_view')[0].addClassName('open');
                }
                else if( $('category_'+attribute.line)){
				
                    $('category_'+attribute.line).next().next().next().value=dfm.mappingStr;
				
                   
                }
            }
        });
        
        $$('.node').each(function(n){
            if(n.select("ul")[0] && n.select('.tree_view.open').length<1){
                n.select("ul")[0].hide();
                n.select('.tree_view')[0].addClassName('close');
            }
            else if (n.select("ul")[0]){
                n.select('.tree_view')[0].addClassName('open');
            }
        })
    }
    else{
           
          
        $$('.mapping').each(function(m){
            m.value=dfm.mappingStr;
                 
        })
        $$('.node').each(function(n){
            if(n.select("ul")[0]){
                n.select('.tree_view')[0].addClassName('close');
                n.select("ul")[0].hide();
            }
        })
    }
       
    $$('.node').each(function(n){
        if(n.select('.tree_view')[0]){
            n.select('.tree_view')[0].observe('click',function(){
                if(n.select('.tree_view')[0].hasClassName('open')){
                    if(n.select("ul")[0]) n.select("ul")[0].hide();
                    n.select('.tree_view')[0].removeClassName('open').addClassName('close');
                }
                else{

                    if(n.select("ul")[0]) n.select("ul")[0].show();
                    n.select('.tree_view')[0].removeClassName('close').addClassName('open');

                }
            })
        }
    })

    $('simplegoogleshopping_type_ids').value.split(',').each(function(e){
        $('type_id_'+e).checked=true;
        $('type_id_'+e).ancestors()[1].addClassName('selected');
    });
	
    $('simplegoogleshopping_visibility').value.split(',').each(function(e){
        $('visibility_'+e).checked=true;
        $('visibility_'+e).ancestors()[1].addClassName('selected');
    });
    if($('simplegoogleshopping_attributes').value=='')$('simplegoogleshopping_attributes').value="[]";
    attributes=$('simplegoogleshopping_attributes').value.evalJSON();

    attributes.each(function(attribute){
		
        if(attribute.checked){
            $('attribute_'+attribute.line).checked=true;
            $('node_'+attribute.line).addClassName('selected');
            $('node_'+attribute.line).select('INPUT:not(INPUT[type=checkbox]),SELECT').each(function(h){
                h.disabled=false
            })
        }
        $('name_attribute_'+attribute.line).value=attribute.code;
        $('condition_attribute_'+attribute.line).value=attribute.condition;
        $('value_attribute_'+attribute.line).value=attribute.value;
    });
    
    $('attributes-selector').select('SELECT').each(function(n){
        if(n.hasClassName('name-attribute')){
            prefilledValues=n.next().next();
            eval("options="+n.value);
            
           html=null;
            custom=true;
            options.each(function(o){
                
                if (prefilledValues.next().value.split(',').indexOf(o.value)!=-1){
                    selected='selected'
                    custom=false;
                }
                else{
                    selected=false;
                }
                
                html+="<option value='"+o.value+"' "+selected+">"+o.label+"</option>";
            })
            if(custom)selected="selected";
            else selected='';
            html+="<option value='_novalue_' style='color:#555' "+selected+">custom value...</option>";
            
            if(options.length>0){
                if(!custom){
                          
                    prefilledValues.setStyle({
                        'display':'inline'
                        
                    });
                    prefilledValues.next().setStyle({
                        'display':'none'
                        
                    }) 
                   /* r=[];
                    prefilledValues.select('OPTION').each(function(e){
                        if(e.selected) r.push(e.value)
                            })
                    r=r.join(',')
                    prefilledValues.next().value=r;
                     */
                }
                else {
                    prefilledValues.setStyle({
                        'display':'inline'
                        
                    });
                    prefilledValues.next().setStyle({
                        'display': 'block',
                        'margin': '0 0 0 422px'
                        
                    }) 
                }
                prefilledValues.update(html)
                
                
                
            }
            
            
            n.observe('change',function(){
               
                prefilledValues=n.next().next();
                eval("options="+n.value);
                html="";
                options.each(function(o){
                    (o.value==prefilledValues.next().value)? selected='selected':selected=null;
                
                    html+="<option value='"+o.value+"' "+selected+">"+o.label+"</option>";
                })
                
                html+="<option value='_novalue_' style='color:#555'>custom value...</option>";
                if(options.length>0){
                   
                    prefilledValues.setStyle({
                        'display':'inline'
                        
                    });
                    prefilledValues.next().setStyle({
                        'display':'none'
                       
                    }) 
                   
                    prefilledValues.update(html)
                    
                   
                }
                else{
                    prefilledValues.setStyle({
                        'display':'none'
                        
                    });
                    prefilledValues.next().setStyle({
                        'display':'inline',
                        'margin': '0 0 0 0'
                       
                    }) 
                    prefilledValues.next().value=null;
                    
                }
                prefilledValues.next().value=null
                setValues($("attributes-selector"))
            })
        }
    })
    $$('.pre-value-attribute').each(function(prefilledValues){
        prefilledValues.observe('change',function(){
                       
            if(prefilledValues.value!='_novalue_'){
                          
                prefilledValues.setStyle({
                    'display':'inline'
                    
                });
                prefilledValues.next().setStyle({
                    'display':'none'
                    
                }) 
                r=[];
                prefilledValues.select('OPTION').each(function(e){
                    if(e.selected) r.push(e.value)
                        })
                r=r.join(',')
                           
                prefilledValues.next().value=r;
                setValues($("attributes-selector"))
               
            }
            else {
                prefilledValues.setStyle({
                    'display':'inline'
                   
                });
                prefilledValues.next().setStyle({
                     'display': 'block',
                     'margin': '0 0 0 422px'
                }) 
                
            }
                       
        })
    })
		
})


function setValues(selector){
    selection=new Array;
    selector.select('INPUT[type=checkbox]').each(function(i){
        if(selector.id=='attributes-selector'){
		
            attribute={}
            attribute.line=i.readAttribute('identifier');
            attribute.checked=i.checked;
            attribute.code=i.next().value;
            attribute.condition=i.next().next().value;
            attribute.value=i.next().next().next().next().value;
            selection.push(attribute);
        }
        else if(selector.id=='category-selector'){
			
            attribute={}
            attribute.line=i.readAttribute('identifier');
            attribute.checked=i.checked;
            attribute.mapping=i.next().next().next().value;
            if(attribute.mapping.trim()=="" || attribute.mapping.trim()==dfm.mappingStr ) attribute.mapping="";
            selection.push(attribute);
				
			
			
        }
        else if(i.checked==true)selection.push(i.readAttribute('identifier'));
		
    })
    switch(selector.id){
        case 'category-selector':
            $('simplegoogleshopping_categories').value=Object.toJSON(selection);
            break;
        case 'type-ids-selector':
            $('simplegoogleshopping_type_ids').value=selection.join(',');
            break;
        case 'visibility-selector':
            $('simplegoogleshopping_visibility').value=selection.join(',');
            break;
        case 'attributes-selector' :
            $('simplegoogleshopping_attributes').value=Object.toJSON(selection);
            break;
    }
	
}

var dfm={
    mappingStr:"empty",	
    /*
	 * Mise � jour des donn�es 
	 * 
	 */
    update:function(){
		
        // nom du fichier
        $('dfm-view').select('.feedname')[0].update($('simplegoogleshopping_filename').value)
		
		

        header = '<?xml version="1.0" encoding="utf-8" ?>\n';
        header+='<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">\n'; 	
        header+='<channel>\n';
        header+='<title>'+$('simplegoogleshopping_title').value+'</title>\n';
        header+='<link>'+$('simplegoogleshopping_url').value+'</link>\n';
        header+='<description>'+$('simplegoogleshopping_description').value+'</description>\n';
		
		
		
        $('dfm-view').select('._header')[0].update(dfm.enlighter(header));
		
        $('dfm-view').select('._footer')[0].update(dfm.enlighter('</channel>'))
		
        value ="<item>\n"
        value+=$('simplegoogleshopping_xmlitempattern').value+"\n";
        value+="</item>\n"
        p='<br><pre class="productpattern">'+dfm.enlighter(value)+'</pre><br>';
		
		
		
        $('dfm-view').select('._product')[0].update(p+p);
		
    },
    /*
	 * Surligenr le code
	 * 
	 */
    enlighter: function(text){
		
		
        // tags
        text=text.replace(/<([^?^!]{1}|[\/]{1})(.[^>]*)>/g,"<span class='blue'>"+"<$1$2>".escapeHTML()+"</span>")
		
        // comments
        text=text.replace(/<!--/g,"¤");
        text=text.replace(/-->/g,"¤");
        text=text.replace(/¤([^¤]*)¤/g,"<span class='green'>"+"<!--$1-->".escapeHTML()+"</span>");
		
        // php code
        text=text.replace(/<\?/g,"¤");
        text=text.replace(/\?>/g,"¤");
        text=text.replace(/¤([^¤]*)¤/g,"<span class='orange'>"+"<?$1?>".escapeHTML()+"</span>");
        // superattribut
        text=text.replace(/\{(G:[^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?\}/g,"<span class='purple'>{$1<span class='grey'>$2</span>$4<span class='green'>$5</span>$7<span class='green'>$8</span>$10<span class='green'>$11</span>$13<span class='green'>$14</span>$16<span class='green'>$17</span>$19<span class='green'>$20</span>}</span>");
        // superattribut 
        text=text.replace(/\{(SC:[^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?\}/g,"<span class='orangered '>{$1<span class='grey'>$2</span>$4<span class='green'>$5</span>$7<span class='green'>$8</span>$10<span class='green'>$11</span>$13<span class='green'>$14</span>$16<span class='green'>$17</span>$19<span class='green'>$20</span>}</span>");
		
        // attributs + 6 options 
        text=text.replace(/\{([^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?((,)(\[.[^\]]*\]))?\}/g,"<span class='pink'>{$1<span class='grey'>$2</span>$4<span class='green'>$5</span>$7<span class='green'>$8</span>$10<span class='green'>$11</span>$13<span class='green'>$14</span>$16<span class='green'>$17</span>$19<span class='green'>$20</span>}</span>");
				
        // attributs + options bool
        text=text.replace(/\{([^\s}[:]*)(\sparent|\sgrouped|\sconfigurable|\sbundle)?(\?)(\[[^\]]*\])(:)(\[[^\]]*\])\}/g,"<span class='pink'>{$1<span class='grey'>$2</span>$3<span class='green'>$4</span>$5<span class='red'>$6</span>}</span>");
		
		
		
        return text;
    }
		
}

/*
 * OBSERVERS
 * 
 */
document.observe('dom:loaded', function(){
	
	
	
    page=Builder.node('div',{
        id:'dfm-view'
    },[
	                              
    Builder.node('span',{
        className:'feedname'
    },'exemple'),
	      
    Builder.node('div',{
        id:'page'
    },[
	         
    Builder.node('pre',{
        className:'_header',
        name:''
    }),
    Builder.node('pre',{
        className:'_product',
        name:''
    }),
    Builder.node('pre',{
        className:'_footer',
        name:''
    })
           
    ])
    ])
    
    $('simplegoogleshopping_form').select('.hor-scroll')[0].insert({
        bottom:page
    });
	
	
    $$('.refresh').each(function(f){
        f.observe('keyup', function(){
            dfm.update()
        })
    })
    dfm.update()
	
	
})
