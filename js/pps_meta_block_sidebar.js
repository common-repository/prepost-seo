// pps_meta_block_sidebar.js

( function( wp ) {

    var registerPlugin = wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var el = wp.element.createElement;
    var CheckboxControl = wp.components.CheckboxControl;
    var Button = wp.components.Button;


        registerPlugin( 'perpost-seo-setting-panel', {
            render: function() {
                return el( PluginDocumentSettingPanel,
                    {
                        name: 'perpost-seo-panel',
                        icon: '../imgs/logo.png',
                        className: 'prepost-seo-panel',
                        title: 'PrePost SEO',
                    },
                    el( 'div',
                        { className: 'pps_btnCheck_box' },
                        el( CheckboxControl, {
                            label: "Contnet Status",
                            className: 'hidden',
                            id: "pps_check_contentStatus",
                            checked: true,
                            onChange: () => {}
                        } ),
                        el( CheckboxControl, {
                            label: "Links Status",
                            id: "pps_check_linksStatus",
                            onChange: () => {}
                        } ),
                        el( CheckboxControl, {
                            label: "Density",
                            id: "pps_check_density",
                            onChange: () => {}
                        } ),
                        el( CheckboxControl, {
                            label: "Check Grammar",
                            id: "pps_check_grammar",
                            onChange: () => {}
                        } ),
                        el( CheckboxControl, {
                            label: "Check Plagiarism",
                            id: "pps_check_plagiarism",
                            onChange: () => {}
                        } ),
                        el( Button, {
                            isPrimary: true,
                            className: "button button-primary button-large sba_btnCheck",
                            id: "AnalyzePost",
                        },
                        'Analyze This Post'
                        )
                    )
                );
            },
        } );
    
        // var prepost_seo_panel = document.getElementsByClassName('prepost-seo-panel');
        // if(prepost_seo_panel.length > 0){
        // 	if(prepost_seo_panel[0].classList.contains('is-opened') === false)
        //         wp.data.dispatch( 'core/edit-post' ).toggleEditorPanelOpened( 'perpost-seo-setting-panel/perpost-seo-panel' ); //prepost_seo_panel[0].click();
        // }
    
    } )( window.wp );
    
    

    