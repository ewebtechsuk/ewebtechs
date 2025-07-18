(function () {

    const ZoomMtgApp = {
        meetingID: atob(lddzm_ajx.meeting_id),
        redirectTo: lddzm_ajx.redirect_page,
        password: lddzm_ajx.meeting_pwd !== false ? atob(lddzm_ajx.meeting_pwd) : false,
        infoContainer: document.querySelector('.ldd-zoom-browser-meeting--info__browser'),
        /**
         * Intialize
         */
        init: function () {
            this.initSDK();
            this.eventHandlers();
        },
        /**
         * Initialize the SDK
         */
        initSDK: function () {
            const browseinfo = ZoomMtg.checkSystemRequirements();
            const unorderedLists = document.createElement('ul');
            let listElements = '<li><strong>' + lddzm_ajx.browser_info + '</strong> ' + browseinfo.browserInfo + '</li>';
            listElements += '<li><strong>' + lddzm_ajx.browser_name + '</strong> ' + browseinfo.browserName + '</li>';
            listElements += '<li><strong>' + lddzm_ajx.browser_version + '</strong> ' + browseinfo.browserVersion + '</li>';
            unorderedLists.innerHTML = listElements;
            this.infoContainer.appendChild(unorderedLists);
            ZoomMtg.preLoadWasm();
            ZoomMtg.prepareWebSDK();
        },
        /**
         * Event Listeners
         */
        eventHandlers: function () {
            let joinMtgButton = document.getElementById('ldd-zoom-browser-meeting-join-mtg');
            if (joinMtgButton != null) {
                joinMtgButton.onclick = this.handleJoinMeetingButton.bind(this);
            }
        },
        /**
         * HTML loader
         *
         * @returns {HTMLSpanElement}
         */
        loader: function () {
            const loaderWrapper = document.createElement('span');
            loaderWrapper.id = 'ldd-meeting-cover';
            return loaderWrapper;
        },
        /**
         * Generate the signature for the webSDK
         *
         * @returns {Promise<any>}
         */
        generateSignature: async function () {
            const postData = new FormData();
            postData.append('action', 'ldd_get_auth');
            postData.append('noncce', lddzm_ajx.security);
            postData.append('meeting_id', parseInt(this.meetingID));
            const response = await fetch(lddzm_ajx.ajaxurl, {
                method: 'POST',
                body: postData
            });
            return response.json();
        },
        /**
         * Remove the loader screen
         */
        removeLoader: function () {
            const cover = document.getElementById('ldd-meeting-cover');
            if (cover !== null) {
                document.getElementById('ldd-meeting-cover').remove();
            }
        },
        /**
         * Handle join meeting button click
         *
         * @param e
         */
        handleJoinMeetingButton: function (e) {
            e.preventDefault();

            //Show Loader
            document.body.appendChild(this.loader());
            const display_name = document.getElementById('ldd-meeting-display-name');
            const email = document.getElementById('ldd-meeting-user-email');
            const pwd = document.getElementById('ldd-meeting-password');
            if (display_name !== null && (display_name.value === null || display_name.value === '')) {
                this.infoContainer.innerHTML = lddzm_ajx.error_name;
                this.infoContainer.style.color = 'red';
                this.removeLoader();
                return false;
            }

            //Email Validation
            if (email !== null && (email.value === null || email.value === '')) {
                this.infoContainer.innerHTML = lddzm_ajx.error_email;
                this.infoContainer.style.color = 'red';
                this.removeLoader();
                return false;
            }

            //Password Validation
            if (pwd !== null && (pwd.value === null || pwd.value === '')) {
                this.infoContainer.innerHTML = lddzm_ajx.error_password;
                this.infoContainer.style.color = 'red';
                this.removeLoader();
                return false;
            }
            if (this.meetingID != null || this.meetingID !== '') {
                this.generateSignature().then(result => {
                    if (result.success) {
                        //remove the loader
                        this.removeLoader();
                        const validatedObjects = {
                            name: display_name !== null ? display_name.value : '',
                            password: pwd !== null ? pwd.value : '',
                            email: email !== null ? email.value : ''
                        };
                        this.prepBeforeJoin(result, validatedObjects);
                    }
                });
            }
        },
        /**
         * Validate the elements before joining the meeting
         *
         * @param response
         * @param validatedObjects
         * @returns {boolean}
         */
        prepBeforeJoin: function (response, validatedObjects) {
            const API_KEY = response.data.key;
            const SIGNATURE = response.data.sig;
            const REQUEST_TYPE = response.data.type;

            //validation complete now remove the main form page and attach zoom screen
            const mainWindow = document.getElementById('ldd-zoom-browser-meeting');
            if (mainWindow !== null) {
                mainWindow.remove();
            }
            const locale = document.getElementById('meeting_lang');

            //Set this for the additional props to pass before the actual meeting
            const meetConfig = {
                lang: locale !== null ? locale.value : 'en-US',
                leaveUrl: this.redirectTo
            };

            //Actual meeting join props
            let meetingJoinParams = {
                meetingNumber: parseInt(this.meetingID, 10),
                userName: validatedObjects.name,
                signature: SIGNATURE,
                userEmail: validatedObjects.email,
                passWord: validatedObjects.password ? validatedObjects.password : this.password,
                success: function (res) {
                    console.log('Join Meeting Success');
                },
                error: function (res) {
                    console.log(res);
                }
            };
            const urlSearchParams = new URLSearchParams(window.location.search);
            const params = Object.fromEntries(urlSearchParams.entries());
            if (params.tk !== null) {
                meetingJoinParams.tk = params.tk;
            }
            if (window.location !== window.parent.location) {
                meetConfig.leaveUrl = window.location.href;
            }
            if (REQUEST_TYPE === 'jwt') {
                meetingJoinParams.apiKey = API_KEY;
            } else if (REQUEST_TYPE === 'sdk') {
                meetingJoinParams.sdkKey = API_KEY;
            }
            this.joinMeeting(meetConfig, meetingJoinParams);
        },
        /**
         * Join the meeting finally
         *
         * @param config
         * @param meetingJoinParams
         */
        joinMeeting: function (config, meetingJoinParams) {

            ZoomMtg.init({
                leaveUrl: config.leaveUrl,
                isSupportAV: true,
                meetingInfo: lddzm_ajx.meetingInfo,
                disableInvite: lddzm_ajx.disableInvite,
                disableRecord: lddzm_ajx.disableRecord,
                disableJoinAudio: lddzm_ajx.disableJoinAudio,
                isSupportChat: lddzm_ajx.isSupportChat,
                isSupportQA: lddzm_ajx.isSupportQA,
                isSupportBreakout: lddzm_ajx.isSupportBreakout,
                isSupportCC: lddzm_ajx.isSupportCC,
                screenShare: lddzm_ajx.screenShare,
                success: function () {
                    ZoomMtg.i18n.load(config.lang);
                    ZoomMtg.i18n.reload(config.lang);
                    ZoomMtg.join(meetingJoinParams);
                },
                error: function (res) {
                    console.log(res);
                }
            });
        }
    };
    document.addEventListener('DOMContentLoaded', ZoomMtgApp.init());
})();