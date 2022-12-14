<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="[%%HTML_LANGUAGE_PARAMETER%%]" xml:lang="[%%HTML_LANGUAGE_PARAMETER%%]">
    <head>
        <!-- Technology by Jahns and Friends AG - www.jahnsandfriends.de -->
        <title>Leukotape┬«</title>
        <meta http-equiv="Content-Type" content="[%%content_type%%]; charset=UTF-8" />
        <meta http-equiv="author" content="Jahns and Friends AG" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="-1" />
        <meta http-equiv="imagetoolbar" content="no" />
        <meta name="description" content="" />
        <meta name="DCSext.Country" content="Germany" />
        <meta name="DCSext.Language" content="german" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- OneTrust Cookies Consent Notice start for leukotape.com -->
        <script type="text/javascript" src="https://cdn-ukwest.onetrust.com/consent/86ec2f38-9311-4019-ac30-f891237013d9-test/OtAutoBlock.js" ></script>
        <script src="https://cdn-ukwest.onetrust.com/scripttemplates/otSDKStub.js" data-document-language="true" type="text/javascript" charset="UTF-8" data-domain-script="86ec2f38-9311-4019-ac30-f891237013d9-test" ></script>
        <script type="text/javascript">
            function OptanonWrapper() {

                const cookieSettingsBtn = document.getElementById("onetrust-pc-btn-handler");
                const acceptBtn = document.getElementById("onetrust-accept-btn-handler");
                const declineBtn = document.querySelector("button#onetrust-reject-all-handler");

                const btnContainer = document.getElementById("onetrust-button-group");
                btnContainer.insertBefore(acceptBtn, cookieSettingsBtn)
                btnContainer.insertBefore(declineBtn, cookieSettingsBtn)

            }
        </script>

        <!-- OneTrust Cookies Consent Notice end for leukotape.com -->

        <link rel="Shortcut Icon" href="[%%baseurl%%]favicon.ico" type="image/vnd.microsoft.icon" />
        <link rel="icon" href="[%%baseurl%%]favicon.ico" type="image/vnd.microsoft.icon" />
        <link rel="shortcut icon" type="image/x-icon" href="[%%baseurl%%]favicon.ico" />
        <link rel="stylesheet" type="text/css" href="[%%asset_base%%]assets/css/bootstrap/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="[%%asset_base%%]assets/css/dropzone.min.css">
        <link rel="stylesheet" type="text/css" href="[%%asset_base%%]assets/css/fontawesome/all.css">
        [##disabled##]
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
            <link href="https://fonts.googleapis.com/css?family=Cabin+Condensed" rel="stylesheet">
        [##disabled##]
        <link rel="stylesheet" type="text/css" href="[%%asset_base%%]assets/css/default.css">
        <script src="[%%asset_base%%]assets/js/jquery/jquery-3.3.1.js"></script>
        <script src="[%%asset_base%%]assets/js/popper.min.js"></script>
        <script src="[%%asset_base%%]assets/js/bootstrap/bootstrap.js"></script>
        [##disabled##]
            <link rel="stylesheet" type="text/css" href="[%%asset_base%%]assets/css/cookieconsent.min.css" />
            <script src="[%%asset_base%%]assets/js/cookieconsent.min.js"></script>
            <script>
                window.addEventListener("load", function(){
                    window.cookieconsent.initialise({
                        "palette": {
                            "popup": {
                                "background": "#004e9e"
                            },
                            "button": {
                                "background": "#85ba35",
                                "text": "#ffffff"
                            }
                        },
                        "position": "bottom-right",
                        "content": {
                            "message": "[Diese Website nutzt Cookies, um bestm├Âgliche Funktionalit├Ąt bieten zu k├Ânnen.|Ce site Web utilise des cookies pour fournir la meilleure fonctionnalit├ę possible.|Questo sito web utilizza i cookie per fornire la migliore funzionalit├á possibile.]",
                            "dismiss": "OK!",
                            "link": "[Mehr erfahren|En savoir plus|Maggiori informazioni]",
                            "href": "[%%baseurl%%]cookies"
                        }
                    })
                });
            </script>
        [##disabled##]
    </head>
    <body>
        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                        <!-- 16:9 aspect ratio -->
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" src="" id="video"  allowscriptaccess="always">></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="wrapper">
            <div class="row">
                <div class="col-12 col-sm-6 py-4 pl-5 text-center text-sm-left">
                    <img src="[%%asset_base%%]assets/img/500_leukotape_logo_rgb.png" width="280" />
                </div>
                [##language_ch##]
                    <div class="col-12 col-sm-6 py-4 pl-0 pl-sm-4 text-center text-sm-right pr-0 pr-sm-5">
                        <div class="btn-group pt-0 pt-sm-2">
                            <button type="button" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                [
                                <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/de.png" /> Deutsch
                                |
                                <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/fr.png" /> Fran├žais
                                |
                                <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/it.png" /> Italiano
                                ]
                            </button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: top, left; top: 38px; left: 0px;">
                                <a class="dropdown-item" href="?cl=de">
                                    <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/de.png" />&nbsp;
                                    [Deutsch|Allemand|Tedesco]
                                </a>
                                <a class="dropdown-item" href="?cl=fr">
                                    <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/fr.png" />&nbsp;
                                    [Franz├Âsisch|Fran├žais|Francese]
                                </a>
                                <a class="dropdown-item" href="?cl=it">
                                    <img class="d-inline pb-1 pr-1" src="[%%asset_base%%]assets/img/flags/it.png" />&nbsp;
                                    [Italienisch|Italien|Italiano]
                                </a>
                            </div>
                        </div>
                    </div>
                [##language_ch##]
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <a href="[%%baseurl%%]" alt="Leukotape┬«" title="Leukotape┬«">
                        [
                            <img class="img-fluid" src="[%%asset_base%%]assets/img/960_415_header.png" title="Leukotape┬« Tapepower" alt="Leukotape┬« Tapepower" style="">
                        |
                            <img class="img-fluid" src="[%%asset_base%%]assets/img/1200_600_Header_fr.jpg" title="Leukotape┬« Tapepower" alt="Leukotape┬« Tapepower" style="">
                        |
                            <img class="img-fluid" src="[%%asset_base%%]assets/img/960_415_header.png" title="Leukotape┬« Tapepower" alt="Leukotape┬« Tapepower" style="">
                        ]
                        
                    </a>
                </div>
            </div>
            
            <div class="row-fluid">
                <div class="col-12">
                    [##countryselect##]
                    <div class="row my-5 d-flex justify-content-center">
                        <div class="col-8 col-md-4">
                            <div class="media">
                                <a href="[%%asset_base%%]at"><img class="mr-3 mt-1" src="[%%asset_base%%]assets/img/flag_at.png" /></a>
                                <div class="media-body">
                                    <h4>&nbsp;</h4>
                                    <h4><a href="[%%asset_base%%]at">├ľsterreich</a></h4>
                                    <h4>&nbsp;</h4>
                                </div>
                            </div>

                        </div>
                        <div class="col-8 col-md-4">
                            <div class="media">
                                <a href="[%%asset_base%%]chd"><img class="mr-3 mt-1" src="[%%asset_base%%]assets/img/flag_ch.png" /></a>
                                <div class="media-body">
                                    <h4><a href="[%%asset_base%%]chd">Schweiz</a></h4>
                                    <h4><a href="[%%asset_base%%]chf">Suisse</a></h4>
                                    <h4><a href="[%%asset_base%%]chi">Svizzera</a></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    [##countryselect##]
                    [##showContent##]
                    [%%content%%]
                    [##showContent##]
                </div>
            </div>
            
            [##formOnly##]
                <div id="anlegetechniken" class="row no-gutters mt-5">
                    <div class="col-12 col-md-6">
                        <div class="card bg-transparent border-0">
                            <div class="card-header pl-4 py-1 text-uppercase">
                                [LEUKOTAPE┬« ANLAGEVIDEOS|LEUKOTAPE┬« - VIDEOS SUR L'APPLICATION|VIDEO DI SISTEMA LEUKOTAPE┬«]
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card bg-transparent border-0">
                            <div class="card-header bg-transparent border-0">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div id="anlegetechniken" class="row no-gutters">
                    <div class="col-12">
                        <div class="card bg-transparent border-0">
                            <div class="card-body ml-1 pb-0">
                                <p>
                                    [
                                    Wie wird Leukotape┬« richtig angelegt und wie tapen echte Profis? Unsere Anlagevideos geben Ihnen hier effektive Taping-Tipps, -Tricks und -Techniken!
                                    |
                                    Quelle est la technique d'application correcte (Leukotape┬«) ? Quelles sont les techniques d'application des vrais professionnels ? 
                                    Nos vid├ęos sur la technique d'application vous apportent des conseils efficaces en mati├Ęre du taping ainsi que des astuces et des techniques !
                                    |
                                    Come viene applicato correttamente Leukotape┬« e come lo fanno i veri professionisti? I nostri video di applicazione vi danno consigli, trucchi e tecniche di applicazione efficaci!
                                    ]
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2 px-0 px-md-4 d-flex justify-content-around videos">
                    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3 videobox">
                        [
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/-uNDj__I71s" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_fr.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/-uNDj__I71s" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_it.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/-uNDj__I71s" data-target="#myModal" />
                        ]
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3 videobox">
                        [
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons2.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/XQkd2TFc9po" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_fr2.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/XQkd2TFc9po" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_it2.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/XQkd2TFc9po" data-target="#myModal" />
                        ]
                        
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3 videobox">
                        [
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons3.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/6QH4qOxTqqY" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_fr3.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/6QH4qOxTqqY" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_it3.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/6QH4qOxTqqY" data-target="#myModal" />
                        ]
                    </div>
                    <!-- <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3 videobox">
                        <img src="./assets/img/320_320_play_buttons4.png" class="img-fluid data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/dXHkV1owtnE" data-target="#myModal" />
                    </div> -->
                    <div class="col-12 col-sm-6 col-md-4 col-lg text-center mb-3 videobox">
                        [
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons5.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/xcmLDkKEzjM" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_fr4.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/xcmLDkKEzjM" data-target="#myModal" />
                        |
                            <img src="[%%asset_base%%]assets/img/320_320_play_buttons_it4.png" class="img-fluid" data-toggle="modal" data-src="https://www.youtube-nocookie.com/embed/xcmLDkKEzjM" data-target="#myModal" />
                        ]
                    </div>
                </div>
            [##formOnly##]
            <div class="footer-toolbar clearfix">
                <div class="row">
                    <div class="col-12 col-md-9 d-flex flex-column justify-content-center">
                        <ul class="px-3">
                            [##formOnly##]
                            <li>
                                <span>
                                    <a href="[%%baseurl%%]terms" target="_blank">[Teilnahmebedingungen|Conditions de participation|Condizioni di partecipazione]</a>
                                </span>
                            </li>
                            <li>
                                <span>
                                    
                                    <a href="[%%baseurl%%]privacy" target="_blank">
                                        [Datenschutzerkl├Ąrung|D├ęclaration de Confidentialit├ę|Dichiarazione di protezione dei dati]
                                    </a>
                                </span>
                            </li>
                            <li>
                                <span>
                                    <a href="[%%baseurl%%]cookies" target="_blank">
                                        [Verwendung von Cookies|Emploi de t├ęmoins de connexion|Utilizzo dei cookie]
                                    </a>
                                </span>
                            </li>
                            <li>
                                <span>
                                    <a href="[%%baseurl%%]imprint" target="_blank">
                                        [Impressum|Mentions l├ęgales|Informazioni editoriali]
                                    </a>
                                </span>
                            </li>
                            [##formOnly##]
                        </ul>
                        <span class="pl-3" style="color:#7F7F7F; margin-left:10px;">
                            [
                            Eine exakte Diagnose durch einen Arzt oder Physiotherapeuten ist die Basis f├╝r die Anlage von Tapes. Bei Fragen wende dich an deinen Arzt, Apotheker oder Physiotherapeuten.
                            |
                            Le diagnostic exact ├á ├ętablir par un m├ędecin ou un physioth├ęrapeute constitue la base pour la pose de bandes ; les contre-indications doivent ├¬tre prises en consid├ęration. Adressez-vous ├á votre m├ędecin, pharmacien ou physioth├ęrapeute en cas de questions.
                            |
                            Una diagnosi esatta da parte di un medico o fisioterapista ├Ę la seguente la base per la creazione di nastri. In caso di domande, si prega di contattare Il vostro medico, farmacista o fisioterapista.
                            ]
                        </span>
                    </div>
                    <div class="col-12 col-md-3 text-center text-md-right pr-4 mt-4 mt-lg-0">
                        <img src="[%%asset_base%%]assets/img/Essity_Leukotape_logo_200px_H.png" width="200" class="img-fluid pr-3" />
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                var $videoSrc;
                $('.videobox').click(function() {
                    $videoSrc = $(this).find("img").data( "src" );
                    console.log($videoSrc);
                });
                // when the modal is opened autoplay it
                $('#myModal').on('shown.bs.modal', function (e) {
                // set the video src to autoplay and not to show related video. Youtube related video is like a box of chocolates... you never know what you're gonna get
                $("#video").attr('src',$videoSrc + "?rel=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1" );
                })
                // stop playing the youtube video when I close the modal
                $('#myModal').on('hide.bs.modal', function (e) {
                // a poor man's stop video
                $("#video").attr('src',$videoSrc);
                })
            });
            if (typeof dropContainer !== "undefined") {
                // dragover and dragenter events need to have 'preventDefault' called
                // in order for the 'drop' event to register.
                // See: https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Drag_operations#droptargets
                dropContainer.ondragover = dropContainer.ondragenter = function(evt) {
                    evt.preventDefault();
                };
                dropContainer.ondrop = function(evt) {
                    // pretty simple -- but not for IE :(
                    fileInput.files = evt.dataTransfer.files;
                    evt.preventDefault();
                };
            }
            var loadFile = function(event) {
                var output = document.getElementById('output');
                output.src = URL.createObjectURL(event.target.files[0]);
                output.classList.remove("invisible");
                output.classList.add("visible");
            };
        </script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=[%%gtag_id%%]"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '[%%gtag_id%%]');
        </script>
    </body>
</html>