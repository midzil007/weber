
/*
 * View360 version 1.4.3
 */

var View360 = function (consObj) {

    this.consObj = consObj;

    this.navigationConfig = {
        btnWidth: 40,
        btnHeight: 40,
        btnMargin: 5,
        showButtons: true,  
        showTool: true,
        showMove: true,
        showRotate: true,
        showPlay: true,
        showPause: true,
        showZoom: true,
        showTurn: true,
        turnSpeed: 40,
        showFullscreen: true,
        showClose: true, // v1.4.3
        align: "center", // left | center | right  // v1.1.0
        orientation: "horizontal", // vertical | horizontal  // v1.1.0
        type: "square", // square | round | custom  // v1.1.0
        cornerRadius: 20, // v 1.1.0
        btnImageSize: "100%"
    };

    this.config = {
        verticalAngles: {
            from: 15,
            to: 45,
            initial: 15
        },
        horizontalAngles: {
            from: 0,
            to: 360,
            initial: 0
        },

        opacity: 1,
        mode: "fixed",
        width: 620,
        height: 350,

        fitmode: "left-right", //v 1.3.0

        oneTurnOnStartUp: true, //v 1.1.0

        autoRotate: false,
        autoRotateDirection: 1,
        autoRotateSpeed: 50,
        autoRotateStopOnMove: true,  

        zoomMultipliers: [1, 1.2, 1.5, 2, 3],

        loadFullSizeImagesOnZoom: true,
        loadFullSizeImagesOnFullscreen: true,

        initialImage: 0, // v1.3.0

        imagesDirectory: ".",
        fullSizeImagesDirectory: "",
        images: [],
        autoLoadImages: true,
        imagesPattern: "image_%COL_%ROW.jpg",
        imagesNumbering: 0, // v1.3.0

        showTurnMeOn: false,  // v1.3.0

        rows: 1,
        columns: 36,
        xAxisSensitivity: 10,
        yAxisSensitivity: 40,
        inertiaConstant: 10,
        panInBounds: false,
        playBtnMode: "loop", //v1.0.9 loop | oneloop | firstimage | lastimage

        opositeDirection: false, //v1.1.1
        loadOnPageLoad: false,//v1.3.0

        firstImageOpacity: 0.5, //v1.1.0
        loadFirstImage: true,//v1.3.0

        imagesNumberingUpDown: 0 //v1.4.0

    };

    this.loaderInfoClass = View360LoadingInfo;
    this.loaderInfoConfig = {};
    this.fullSizeLoaderInfoClass = View360LoadingInfo;
    this.fullSizeLoaderInfoConfig = {
        display: false,
        holderClassName: "View360-fullSizeLoader",
        circleLineWidth: 25,
        circleLineColor: "#AAAAAA",
        modalOpacity: 0,
        loadingTitle: "Loading fullsize images...",
        circleWidth: 50
    };

    this.addCloseToDom = function (holder) {
        holder.innerHTML +=  this.closeButtons.close;
    };

    this.addNavButtonsToDom = function (holder) {

        var html = "";
        for (var i = 0; i < this.navButtonsOrder.length; i++) {
            var alias = this.navButtonsOrder[i];
            html += this.navButtons[alias];
        }
        holder.innerHTML = html;

    };

    this.addNavButton = function (alias, html) {
        this.navButtons[alias] = html;
        this.navButtonsOrder.push(alias);
    };

    this.navButtons = {
        "rotate": '<div class="View360-navigationRotate View360-navigationBtn"></div>',
        "move": '<div class="View360-navigationHand View360-navigationBtn"></div>',
        "play": '<div class="View360-navigationPlay View360-navigationBtn"></div>',
        "pause": '<div class="View360-navigationPause View360-navigationBtn"></div>',
        "turn-left": '<div class="View360-navigationTurnLeft View360-navigationBtn"></div>',
        "turn-right": '<div class="View360-navigationTurnRight View360-navigationBtn"></div>',
        "zoom-in": '<div class="View360-navigationZoomIn View360-navigationBtn"></div>',
        "zoom-out": '<div class="View360-navigationZoomOut View360-navigationBtn"></div>',
        "fullscreen": '<div class="View360-navigationFullscreenEnter View360-navigationBtn"></div><div class="View360-navigationFullscreenExit View360-navigationBtn"></div>'
    };
    this.closeButtons = {
        "close": '<div class="View360-navigationClose View360-navigationBtn"></div>'
    }
   /*  this.closeButtons = {
        "rotate": '<div class="View360-navigationRotate View360-navigationBtn"></div>',*/

    this.navButtonsOrder = ["rotate", "move", "play", "pause", "turn-left", "turn-right", "zoom-in", "zoom-out", "fullscreen"];
    this.setNavButtonsOrder = function (navButtonsOrder) {
        this.navButtonsOrder = navButtonsOrder;
    };

    this.htmlTemplate = ' ';
    this.htmlTemplate += '   <div class="View360-holder"> ';
    this.htmlTemplate += '       <div class="View360-canvasHolder"> ';
    this.htmlTemplate += '           <canvas class="View360-canvas"></canvas> ';
    this.htmlTemplate += '       </div> ';
    this.htmlTemplate += '       <div class="View360-borderHolder View360-loadingBackground"></div> ';
    this.htmlTemplate += '       <div class="View360-loaderHolder"></div> ';
    this.htmlTemplate += '       <div class="View360-fullSizeLoaderHolder"></div> ';
    this.htmlTemplate += '       <div class="View360-clickableArea"></div> ';
    this.htmlTemplate += '<div class="View360-turnMeOnHolder"><div class="View360-turnMeOn"></div></div>';

    this.htmlTemplate += '<div class="View360-closeHolder">';
    this.htmlTemplate += '</div> ';

    this.htmlTemplate += '<div class="View360-navigationHolder">';
    this.htmlTemplate += '<div class="View360-navigation">';

    this.htmlTemplate += '</div> ';
    this.htmlTemplate += '</div> ';
    this.htmlTemplate += '       <div class="View360-close"></div> ';
    this.htmlTemplate += '   </div>';

    this.globalPanMoveX = 0;
    this.globalPanMoveY = 0;
    this.currentPanMoveX = 0;
    this.currentPanMoveY = 0;
    this.currentZoom = 1;

    this.isFullscreen = false;

    this.ctx = null;
    this.totalImages = 0;

    this.cols;
    this.rows;
    this.totalImages;

    this.autoRotatePaused = false;
    this.mouseDown = false;
    this.refMouseX;
    this.refMouseY;
    this.refCol;
    this.refRow;
    this.currentCol = 0;
    this.currentRow = 0;
    this.refCol = this.currentCol;
    this.refRow = this.currentRow;

    this.autoCol = 0;

    this.images = [];

    this.clickableAreaObj = null;

    this.oldBodyOverflowX="unset";

    this.rememberInertiaInterval = null;
    this.inertiaStatesArr = [];
    this.stopRememberInertiaInterval = function () {
        clearInterval(this.rememberInertiaInterval);
    };

    this.inertiaPrevStateX = null;
    this.inertiaPrevStateY = null;
    this.allowInertia = false;
    this.startRememberInertiaInterval = function () {
        var self = this;
        this.inertiaStatesArr = [];
        this.rememberInertiaInterval = setInterval(function () {

            if (self.inertiaPrevStateX == self.currentMoveX && self.inertiaPrevStateY == self.currentMoveY) {
                self.allowInertia = false;
            } else {
                self.allowInertia = true;
            }

            self.inertiaPrevStateX = self.currentMoveX;
            self.inertiaPrevStateY = self.currentMoveY;

        }, 30);
    };

    this.rememberInertiaStates = function () {

        var self = this;
        if (self.inertiaStatesArr.length == 0) {

            self.inertiaStatesArr.push({
                moveX: self.currentMoveX,
                moveY: self.currentMoveY
            });
            self.inertiaStatesArr.push({
                moveX: self.currentMoveX,
                moveY: self.currentMoveY
            });
            self.inertiaStatesArr.push({
                moveX: self.currentMoveX,
                moveY: self.currentMoveY
            });
            self.inertiaStatesArr.push({
                moveX: self.currentMoveX,
                moveY: self.currentMoveY
            });
            self.inertiaStatesArr.push({
                moveX: self.currentMoveX,
                moveY: self.currentMoveY
            });

        }

        var newItem = {
            moveX: self.currentMoveX,
            moveY: self.currentMoveY
        };

        self.inertiaStatesArr.push({
            moveX: self.currentMoveX,
            moveY: self.currentMoveY
        });
        self.inertiaStatesArr.shift();

    };


    this.inertiaAutoMoveInterval = null;
    this.startInertiaAutoMoveInterval = function (initial) {

        var self = this;

        if (initial) {
            var diff = 24;
        } else {
            if (!this.allowInertia) {
                this.autoRotatePaused = false;
                return;
            }

            this.stopInertiaAutoMoveInterval();


            try {
                var diff = self.inertiaStatesArr[self.inertiaStatesArr.length - 1].moveX - self.inertiaStatesArr[0].moveX
                if (diff == 0) {
                    return;
                }
            } catch (ex) {
                return;
            }

            diff = diff / 10;
            diff = self.getDirection(diff) * Math.min(15, Math.abs(diff));

        }

        this.refCol = this.currentCol;
        this.refRow = this.currentRow;

        var initialVelocity = Math.abs(diff);
        var scaleFactor = 1.5;  // 1 je ok
        var amplitude = initialVelocity * scaleFactor;
        var step = 0;

        var timeConstant = 6;  // 3 je ok

        var updateInterval = 10; // 10 je ok
        var position = 0;

        this.inertiaAutoMoveInterval = setInterval(function () {
            var delta = amplitude / timeConstant;
            position += delta;
            amplitude -= delta;
            step += 1;
            if (step > 6 * timeConstant) {
                self.autoRotatePaused = false;
                clearInterval(self.inertiaAutoMoveInterval);
            }

            self.autoColMove(self.getDirection(diff) * Math.round(position))

        }, updateInterval);

    };

    this.stopInertiaAutoMoveInterval = function () {
        clearInterval(this.inertiaAutoMoveInterval);
    }

    this.autoColMove = function (moveCol) {

        if (moveCol == 0)
            return;

        var dirX = -1; //- this.getDirection(moveCol);
        this.currentCol = (this.refCol + (dirX * moveCol)) % this.cols;
        this.currentCol = this.currentCol < 0 ? this.currentCol + this.cols : this.currentCol;

        this.autoCol = this.currentCol;

        var idx = this.getIdxByColRow(this.currentCol, this.currentRow);

        this.displayImageByIdx(idx);

    };

    this.mouseDownHandler = function (x, y) {


        var self = this;

        if (this.config.hideOnMouseTurnMeOn) {
            V360Util.addClass(this.turnMeOnHolder, "View360-opacity0");
        }

        if (this.config.autoRotateStopOnMove) {
            this.stopAutoRotate();
        }

        this.stopInertiaAutoMoveInterval();
        this.startRememberInertiaInterval();

        this.refCol = this.currentCol;
        this.refRow = this.currentRow;
        this.refMouseX = x;
        this.refMouseY = y;

        if (this.mouseDown)
            return;

        this.autoRotatePaused = true;
        this.mouseDown = true;

        V360Util.removeEvent(document, "touchmove", this.touchMoveHandlerListener);
        this.touchMoveHandlerListener = function (event) {
            if (event.touches.length > 1) {
                return;
            }

            event.preventDefault();
            self.mouseMoveHandler(event.touches[0].pageX, event.touches[0].pageY);

        };

        V360Util.addEvent(document, "touchmove", this.touchMoveHandlerListener, false);

        V360Util.removeEvent(document, "mousemove", this.mouseMoveHandlerListener);
        this.mouseMoveHandlerListener = function (event) {
            self.mouseMoveHandler(event.pageX, event.pageY);
        };

        V360Util.addEvent(document, "mousemove", this.mouseMoveHandlerListener, false);

        // mouseup
        V360Util.removeEvent(document, "mouseup", this.mouseUpHandlerListener);
        V360Util.removeEvent(this.clickableAreaObj, "touchend", this.mouseUpHandlerListener);
        this.mouseUpHandlerListener = function (event) {
            self.mouseUpHandler(event);
        };

        V360Util.addEvent(document, "mouseup", this.mouseUpHandlerListener, false);
        V360Util.addEvent(this.clickableAreaObj, "touchend", this.mouseUpHandlerListener, false);

    };

    this.setRelativePosition = function (x, y) {
        this.globalPanMoveX = x;
        this.globalPanMoveY = y;
    };

    this.mouseUpHandler = function (evt) {

        evt.stopPropagation();

        this.mouseDown = false;

        this.currentPanMoveX = 0;
        this.currentPanMoveY = 0;


        if (this.isPanMode()) {
            this.globalPanMoveX = -((this.zoomedXPosMoved - this.zoomedXPosCentered - this.panMoveEmptyX) / this.currentZoom); //fullscreenZoom
            this.globalPanMoveY = -((this.zoomedYPosMoved - this.zoomedYPosCentered - this.panMoveEmptyY) / this.currentZoom); //fullscreenZoom
            this.panMoveEmptyX = 0;
            this.panMoveEmptyY = 0;
        }

        V360Util.removeEvent(document, "mousemove", this.mouseMoveHandlerListener);
        V360Util.removeEvent(document, "touchmove", this.touchMoveHandlerListener);
        V360Util.removeEvent(document, "mouseup", this.mouseUpHandlerListener);
        V360Util.removeEvent(this.clickableAreaObj, "touchend", this.mouseUpHandlerListener);

        this.stopRememberInertiaInterval();
        this.startInertiaAutoMoveInterval();
    };

    this.setMoveMode = function (moveMode) {
        this.moveMode = moveMode;
    };
    this.isPanMode = function () {
        return this.moveMode == "PAN";
    };
    this.isRotate = function () {
        return this.moveMode == "ROTATE";
    };
    this.mouseMoveHandler = function (x, y) {
        if (!this.smallLoaded)
            return;
        if (this.isPanMode()) {

            this.currentPanMoveX = this.refMouseX - x;
            this.currentPanMoveY = this.refMouseY - y;
            this.displayCurrentImage();
            return;
        }

        var moveX = this.currentMoveX = this.refMouseX - x;
        var moveY = this.currentMoveY = this.refMouseY - y;

        this.moveObjectByXY(moveX, moveY);

        this.rememberInertiaStates();

    };

    this.moveObjectByXY = function (moveX, moveY) {


        if (moveX !== 0) {


            var dirX = -this.getDirection(moveX);

            var moveCols = Math.floor(Math.abs(moveX) / this.config.xAxisSensitivity);

            this.currentCol = (this.refCol + (dirX * moveCols)) % this.cols;

            this.currentCol = this.currentCol < 0 ? this.currentCol + this.cols : this.currentCol;

        }

        if (moveY !== 0) {
            var dirY = -this.getDirection(moveY);
            var moveRows = Math.floor(Math.abs(moveY) / this.config.yAxisSensitivity);
            this.currentRow = (this.refRow + (dirY * moveRows)); // % rows;
            this.currentRow = Math.min(this.rows - 1, this.currentRow);
            this.currentRow = Math.max(0, this.currentRow);
        }

        this.autoCol = this.currentCol;

        var idx = this.getIdxByColRow(this.currentCol, this.currentRow);
        this.displayImageByIdx(idx);

    };

    this.getIdxByColRow = function (currentCol, currentRow) {
        //if(this.config.opositeDirection == true) currentCol = this.cols - currentCol;
        return currentCol + currentRow * this.cols; // - 1;
    };

    this.getDirection = function (n) {
        return n >= 0 ? 1 : -1;
    };

    this.autoRotateFunc = function (autoRotateDir) {

        var dir = autoRotateDir ? autoRotateDir : this.config.autoRotateDirection;
        if (dir == 1) {
            if (this.autoCol == this.cols - 1)
                this.autoCol = -1;
            this.autoCol++;
        } else {
            if (this.autoCol == 0)
                this.autoCol = this.cols - 1;
            this.autoCol--;
        }

        this.currentCol = this.autoCol;
        var idx = this.getIdxByColRow(this.autoCol, this.currentRow);
        this.displayImageByIdx(idx);


        if (this.config.playBtnMode == "loop")
            return;
        if (!this._isPlayBtn)
            return;

        if (this.config.playBtnMode == "oneloop") {
            this._oneLoopCount++;
            if (this._oneLoopCount >= this.cols) {
                this.stopAutoRotate();
            }
        } else if (this.config.playBtnMode == "firstimage") {
            if (this.autoCol == 0) {
                this.stopAutoRotate();
            }
        } else if (this.config.playBtnMode == "lastimage") {
            if (this.autoCol == this.cols - 1) {
                this.stopAutoRotate();
            }
        } else if (parseInt(this.config.playBtnMode) == this.config.playBtnMode) {
            if (this.autoCol == parseInt(this.config.playBtnMode)) {
                this.stopAutoRotate();
            }
        }

    };

    this.setRow = function (row) {
        if (!this.smallLoaded)
            return;
        this.currentRow = row < this.config.rows ? row : row % this.config.rows;
        var idx = this.getIdxByColRow(this.currentCol, this.currentRow);
        this.displayImageByIdx(idx);
    };

    this.setColumn = function (col) {

        if (!this.smallLoaded)
            return;

        col = Math.abs(col) < this.config.columns ? col : col % this.config.columns;

        if (col < 0)
            col = this.config.columns + col;

        this.currentCol = col;

        var idx = this.getIdxByColRow(this.currentCol, this.currentRow);
        this.displayImageByIdx(idx);

    };

    this.minMax = function (n, min, max) {
        n = Math.min(n, max);
        n = Math.max(n, min);

        return n;
    };

    this.setVerticalAngle = function (a) {

        a = Math.abs(a);
        a = this.minMax(a, this.config.verticalAngles.from, this.config.verticalAngles.to);
        var pos = a - this.config.verticalAngles.from;
        var full = this.config.verticalAngles.to - this.config.verticalAngles.from;

        var proc = (pos / full) * 100;

        var r = Math.floor(this.config.rows * (proc / 100));
        r = Math.min(r, this.config.rows - 1);

        this.setRow(r);

    };

    this.displayCurrentImage = function () {
        if (this.latestImageIdx !== undefined) {
            this.displayImageByIdx(this.latestImageIdx);
        }
    };

    this._getFullSizeImageByIdx = function (idx) {
        // if(this.config.opositeDirection == true) idx = this.images.length - idx - 1;
        return this.fullSizeImages[idx];
    };

    this.displayImageByIdx = function (idx) {

        //if( this.latestImageIdx ==idx) return;

        this.latestImageIdx = idx;

        // if(this.config.opositeDirection == true) idx = this.images.length - idx - 1;

        var imgObj = this.images[idx];

        if (this.fullSizeImages && this.fullSizeImages[idx] && this.fullSizeImages[idx].complete) {
            var imgObj = this.fullSizeImages[idx];
        }

        this.displayImage(imgObj);

    };

    this.displayImage = function (imgObj, opacity) {

        this.latestImageObj = imgObj;
        this.fitImageOn(this.canvas, imgObj);
        //this._drawImage()

    };

    this.panMoveEmptyX = 0;
    this.panMoveEmptyY = 0;
    this.fitImageOn = function (canvas, iobj, skipClear) {

        var imageAspectRatio = iobj.width / iobj.height;
        var canvasAspectRatio = canvas.width / canvas.height;
        var renH, renW, xStart, yStart;

        if (imageAspectRatio > canvasAspectRatio) {
            if (this.config.fitmode == "left-right" || this.config.fitmode == "fit") {
                renW = canvas.width;
                renH = iobj.height * (renW / iobj.width);
                xStart = 0;
                yStart = (canvas.height - renH) / 2;
            } else {
                renH = canvas.height;
                renW = iobj.width * (renH / iobj.height);
                xStart = (canvas.width - renW) / 2;
                yStart = 0;

            }
        }
        else if (imageAspectRatio < canvasAspectRatio) {
            if (this.config.fitmode == "left-right") {
                renW = canvas.width;
                renH = iobj.height * (renW / iobj.width);
                xStart = 0;
                yStart = (canvas.height - renH) / 2;
            } else { // "top-bottom" || "fit"
                renH = canvas.height;
                renW = iobj.width * (renH / iobj.height);
                xStart = (canvas.width - renW) / 2;
                yStart = 0;
            }
        }
        else {
            renH = canvas.height;
            renW = canvas.width;
            xStart = 0;
            yStart = 0;
        }

        var zoomedRenW = renW * this.currentZoom;
        var zoomedRenH = renH * this.currentZoom;

        var zoomedXPos = xStart - (zoomedRenW - renW) / 2;
        var zoomedYPos = yStart - (zoomedRenH - renH) / 2;

        this.zoomedXPosCentered = zoomedXPos;
        this.zoomedYPosCentered = zoomedYPos;


        var fullscreenZoom = 1;

        zoomedXPos -= this.currentPanMoveX + (this.globalPanMoveX * this.currentZoom * fullscreenZoom);
        zoomedYPos -= this.currentPanMoveY + (this.globalPanMoveY * this.currentZoom * fullscreenZoom);

        this.zoomedXPosMoved = zoomedXPos;
        this.zoomedYPosMoved = zoomedYPos;

        if (this.zoomChanged) {
            this.zoomChanged = false;

            this.globalPanMoveX = -((this.zoomedXPosMoved - this.zoomedXPosCentered) / this.currentZoom);
            this.globalPanMoveY = -((this.zoomedYPosMoved - this.zoomedYPosCentered) / this.currentZoom);
        }

        var ctx = canvas.getContext("2d");
        if (!skipClear) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        if (this.config.panInBounds) {
            if (zoomedXPos > 0) {
                this.panMoveEmptyX = zoomedXPos;
                zoomedXPos = 0;
            }
            if (zoomedYPos > 0) {
                this.panMoveEmptyY = zoomedYPos;
                zoomedYPos = 0;
            }


            if (zoomedRenW + zoomedXPos < this.canvas.width) {
                this.panMoveEmptyX = zoomedXPos;
                zoomedXPos = -(zoomedRenW - this.canvas.width);
                this.panMoveEmptyX -= zoomedXPos;
            }
            if (zoomedRenH + zoomedYPos < this.canvas.height) {
                this.panMoveEmptyY = zoomedYPos;
                zoomedYPos = -(zoomedRenH - this.canvas.height);
                this.panMoveEmptyY -= zoomedYPos;
            }

        }

        ctx.save();
        ctx.globalAlpha = this.config.opacity;
        ctx.drawImage(iobj, zoomedXPos, zoomedYPos, zoomedRenW, zoomedRenH);
        ctx.restore();
    }

    // mozfullscreenchange fullscreenchange
    this.addFullScreenEvents = function () {

        var self = this;
        V360Util.removeEvent(window, 'resize', this.windowResize);
        V360Util.removeEvent(document.documentElement, 'fullscreenchange', this.fullscreenEnteredListener);
        V360Util.removeEvent(document, 'mozfullscreenchange', this.fullscreenEnteredListener);
        V360Util.removeEvent(document, 'MSFullscreenChange', this.fullscreenEnteredListener, false);
        V360Util.removeEvent(document.documentElement, 'webkitfullscreenchange', this.fullscreenEnteredListener);
        this.fullscreenEnteredListener = function () {
            self.isFullscreen = document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen || document.msFullscreenElement;
            self.windowResize();
            /*setTimeout(function(){
                self.windowResize();
            },200);*/
        }

        this.windowResize = function () {

            if (self.isFullscreen) {

                self.disableResponsive = true;
                self.latestRWidth = -1; //reset to pass arange
                var bodyElems = document.getElementsByTagName("body");
                var body = bodyElems[0];
                if(self.oldBodyOverflowX=="unset") {
                    self.oldBodyOverflowX = body.style.overflowX;
                    self.oldBodyOverflowY = body.style.overflowY;
                }
               
                //document.documentElement.style.overflowX = "hidden";
                //document.documentElement.style.overflowY = "hidden";
                body.style.overflowX = "hidden";
                body.style.overflowY = "hidden";

                self.innerHolder.className = "View360-holderFullscreen";
                document.body.appendChild(self.innerHolder);

                var fullscreenZoomX = self.canvas.width / window.innerWidth;
                var fullscreenZoomY = self.canvas.height / window.innerHeight;

                self.globalPanMoveX = -((self.zoomedXPosMoved - self.zoomedXPosCentered) / self.currentZoom) / fullscreenZoomX;
                self.globalPanMoveY = -((self.zoomedYPosMoved - self.zoomedYPosCentered) / self.currentZoom) / fullscreenZoomY;

                V360Util.setAttribute(self.canvas, "width", window.innerWidth);
                V360Util.setAttribute(self.canvas, "height", window.innerHeight);
                self.innerHolder.style.width = window.innerWidth + "px";
                self.innerHolder.style.height = window.innerHeight + "px";
                self.innerHolder.style.top = window.pageYOffset + "px";
                self.innerHolder.style.left = window.pageXOffset + "px";

                if (self.navigationConfig.showFullscreen && self.navigationConfig.showButtons) {
                    V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenEnter").style.display = "none";
                    V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenExit").style.display = "inline-block";
                }

                if (self.config.loadFullSizeImagesOnFullscreen) {
                    self.startFullSizeImageLoader();
                }

            } else {

                self.disableResponsive = false;
                self.innerHolder.className = "View360-holder";
                self.holder.appendChild(self.innerHolder);

                var fullscreenZoomX = self.canvas.width / self.config.width;
                var fullscreenZoomY = self.canvas.height / self.config.height;

                self.globalPanMoveX = -((self.zoomedXPosMoved - self.zoomedXPosCentered) / self.currentZoom) / fullscreenZoomX;
                self.globalPanMoveY = -((self.zoomedYPosMoved - self.zoomedYPosCentered) / self.currentZoom) / fullscreenZoomY;

                V360Util.setAttribute(self.canvas, "width", self.config.width);
                V360Util.setAttribute(self.canvas, "height", self.config.height);
                self.innerHolder.style.width = self.config.width + "px";

                self.innerHolder.style.height = self.config.height + "px";
                self.innerHolder.style.top = "0px";
                self.innerHolder.style.left = "0px";


                var bodyElems = document.getElementsByTagName("body");
                var body = bodyElems[0];
                //document.documentElement.style.overflowX = document.documentElement.style.overflowY = "auto"
                body.style.overflowX = self.oldBodyOverflowX;
                body.style.overflowY = self.oldBodyOverflowY;
                setTimeout(function(){
                    body.style.overflowX = self.oldBodyOverflowX;
                    body.style.overflowY = self.oldBodyOverflowY;
                }, 200)


                if (self.navigationConfig.showFullscreen && self.navigationConfig.showButtons) {
                    V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenEnter").style.display = "inline-block";
                    V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenExit").style.display = "none";
                }

            }

            if (self.latestImageIdx !== undefined) {
                self.displayImageByIdx(self.latestImageIdx);
            }

        }
        V360Util.addEvent(window, 'resize', this.windowResize, false);
        V360Util.addEvent(document.documentElement, 'fullscreenchange', this.fullscreenEnteredListener, false);
        V360Util.addEvent(document, 'mozfullscreenchange', this.fullscreenEnteredListener, false);
        V360Util.addEvent(document, 'MSFullscreenChange', this.fullscreenEnteredListener, false);
        V360Util.addEvent(document.documentElement, 'webkitfullscreenchange', this.fullscreenEnteredListener, false);

    };

    this.destroy = function () {

        V360Util.removeEvent(window, 'resize', this.windowResize);
        V360Util.removeEvent(document.documentElement, 'fullscreenchange', this.fullscreenEnteredListener);
        V360Util.removeEvent(document, 'mozfullscreenchange', this.fullscreenEnteredListener);
        V360Util.removeEvent(document, 'MSFullscreenChange', this.fullscreenEnteredListener, false);
        V360Util.removeEvent(document.documentElement, 'webkitfullscreenchange', this.fullscreenEnteredListener);

        if (this.responsiveInterval) clearInterval(this.responsiveInterval);

        this.windowResize = null;
        this.fullscreenEnteredListener = null;
        this.innerHolder = null;
        this.turnMeOnHolder = null;
        this.holder.innerHTML = "";
        this.holder = null;
    };

    this.addInterface = function () {


        this.holder.innerHTML = this.htmlTemplate;
        this.addNavButtonsToDom(V360Util.getFirstElementByClassName(this.holder, "View360-navigation"));
        
        if(this.config.mode.indexOf("lightbox")!=-1){
            this.addCloseToDom(V360Util.getFirstElementByClassName(this.holder, "View360-closeHolder"));
        }

        this.innerHolder = V360Util.getFirstElementByClassName(this.holder, "View360-holder");

        this.turnMeOnHolder = V360Util.getFirstElementByClassName(this.holder, "View360-turnMeOnHolder");
        this.turnMeOnHolder.style.display = "none";





        var el = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationFullscreenExit");
        el.style.display = "none";

        var els = V360Util.getElementsByClassName(this.innerHolder, "View360-navigationBtn");

        for (var i = 0; i < els.length; i++) {
            els[i].style.width = this.navigationConfig.btnWidth + "px";
            els[i].style.height = this.navigationConfig.btnHeight + "px";
            //els[i].style.backgroundSize = this.navigationConfig.btnWidth + "px" + " " + this.navigationConfig.btnHeight + "px";
            els[i].style.backgroundSize = this.navigationConfig.btnImageSize + " " + this.navigationConfig.btnImageSize;


            if (this.navigationConfig.type == "custom") {
                els[i].style.borderRadius = this.navigationConfig.cornerRadius + "px";
            } else if (this.navigationConfig.type == "round") {
                els[i].style.borderRadius = (this.navigationConfig.btnWidth / 2) + "px";
                //els[i].style.backgroundSize = "80% 80%";
            }

            if (this.navigationConfig.btnMargin !== undefined)
                els[i].style.marginLeft = this.navigationConfig.btnMargin + "px";
            if (this.navigationConfig.orientation == "vertical") {
                if (els[i].style.display != "none")
                    els[i].style.display = "block";
            }
        }
        var elNavigation = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigation");


        var elNavigationHolder = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationHolder");

        // elNavigation.style.height =  this.navigationConfig.btnHeight + "px";
        elNavigation.style.textAlign = this.navigationConfig.align;

        if (this.navigationConfig.orientation == "vertical") {
            if (this.navigationConfig.align == "left") {
                elNavigationHolder.style.right = "auto";
            } else if (this.navigationConfig.align == "right") {
                elNavigationHolder.style.left = "auto";
            }
        }

        var self = this;
        this.bindBtn("View360-navigationFullscreenEnter", "click", function (evt) {

            var divObj = evt.target;

            V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenEnter").style.display = "none";
            V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenExit").style.display = "inline-block";

            self.addFullScreenEvents();


            var el = document.documentElement;
            var rfs = el.requestFullScreen
                || el.webkitRequestFullScreen
                || el.mozRequestFullScreen
                || el.msRequestFullscreen;

            var allowedFs = self.isAllowedFS();

            if (!rfs || !allowedFs) {
                self.isFullscreen = true;
                self.windowResize();
            } else {
                rfs.call(el);

            }

        });



        this.bindBtn("View360-navigationFullscreenExit", "click", this.exitFullscreenBtnHandler.bind(this));


    };


         this.exitFullscreenBtnHandler = function () {

            V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationFullscreenEnter").style.display = "inline-block";
            V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationFullscreenExit").style.display = "none";

            this.addFullScreenEvents();

            var el = document
            var rfs = el.exitFullscreen
                || el.webkitCancelFullScreen
                || el.webkitExitFullscreen
                || el.mozCancelFullScreen
                || el.msExitFullscreen;

            var allowedFs = this.isAllowedFS();

            if (!rfs || !allowedFs) {
                this.isFullscreen = false;
                this.windowResize();
            } else {
                rfs.call(el);
            }
        }

    this.close = function () {

        clearInterval(this.rememberInertiaInterval);
        clearInterval(this.autoRotateInterval);
        clearInterval(this.responsiveInterval);
        clearInterval(this.inertiaAutoMoveInterval);


        this.holder.innerHTML = "";

    };

    this.isAllowedFS = function () {
        return document.fullscreenEnabled ||
            document.webkitFullscreenEnabled ||
            document.mozFullScreenEnabled ||
            document.msFullscreenEnabled;
    };

    this.bindBtn = function (className, event, handler) {
        var el = V360Util.getFirstElementByClassName(this.innerHolder, className);
        V360Util.addEvent(el, event, function (event) {
            handler(event);
        }, false);
    }

    this.bindEvents = function () {

        var self = this;

        this.clickableAreaObj = V360Util.getFirstElementByClassName(this.innerHolder, "View360-clickableArea");

        //mousedown
        this.mouseDownHandlerListener = function (event) {
            event.preventDefault();
            self.mouseDownHandler(event.pageX, event.pageY);
        }
        V360Util.addEvent(this.clickableAreaObj, "mousedown", this.mouseDownHandlerListener, false);
        V360Util.addEvent(this.clickableAreaObj, "click", function (evt) {
            evt.stopPropagation();
        }, false);


        //touchstart
        this.touchStartHandlerListener = function (event) {
            self.mouseDownHandler(event.touches[0].pageX, event.touches[0].pageY);
        }
        V360Util.addEvent(this.clickableAreaObj, "touchstart", this.touchStartHandlerListener, false);


        this.navigationHolder = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationHolder");
        // this.navigationHolder.style.display = "none";
        this.navigationHolder.style.visibility = "hidden";

        if (!this.navigationConfig.showButtons) {
            return;
        }


        //TOOL ROTATE
        var rotateBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationRotate");
        if (this.navigationConfig.showTool && this.navigationConfig.showRotate && this.navigationConfig.showButtons) {

            this.navigationHandHandlerListener = function (event) {
                self.setMoveMode("ROTATE");
            }
            V360Util.addEvent(rotateBtn, "click", this.navigationHandHandlerListener, false);
        } else {
            rotateBtn.style.display = "none";
        }

        //TOOL HAND
        var handBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationHand");
        if (this.navigationConfig.showTool && this.navigationConfig.showMove && this.navigationConfig.showButtons) {

            this.navigationHandHandlerListener = function (event) {
                self.setMoveMode("PAN");
            }
            V360Util.addEvent(handBtn, "click", this.navigationHandHandlerListener, false);
        } else {
            handBtn.style.display = "none";
        }

        //PLAY
        var playBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationPlay");
        if (this.navigationConfig.showPlay && this.navigationConfig.showButtons) {

            this.navigationPlayHandlerListener = function (event) {
                self.startAutoRotate(undefined, undefined, true);
            }
            V360Util.addEvent(playBtn, "click", this.navigationPlayHandlerListener, false);
        } else {
            playBtn.style.display = "none";
        }

        //PAUSE
        var pauseBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationPause");
        if (this.navigationConfig.showPause && this.navigationConfig.showButtons) {

            this.navigationPauseHandlerListener = function (event) {
                self.stopAutoRotate();
            }
            V360Util.addEvent(pauseBtn, "click", this.navigationPauseHandlerListener, false);
        } else {
            pauseBtn.style.display = "none";
        }

        //ZOOM IN
        var zoomInBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationZoomIn");
        if (this.navigationConfig.showZoom && this.navigationConfig.showButtons) {

            this.navigationZoomInHandlerListener = function (event) {
                self.zoomIn();
            }
            V360Util.addEvent(zoomInBtn, "click", this.navigationZoomInHandlerListener, false);
        } else {
            zoomInBtn.style.display = "none";
        }

        //ZOOM OUT
        var zoomOutBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationZoomOut");
        if (this.navigationConfig.showZoom && this.navigationConfig.showButtons) {

            this.navigationZoomOutHandlerListener = function (event) {
                self.zoomOut();
            }
            V360Util.addEvent(zoomOutBtn, "click", this.navigationZoomOutHandlerListener, false);
        } else {
            zoomOutBtn.style.display = "none";
        }

        //TURN LEFT
        var turnLeftBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationTurnLeft");
        if (this.navigationConfig.showTurn && this.navigationConfig.showButtons) {

            this.navigationTurnLeftMouseDownHandlerListener = function (event) {

                self.startAutoRotate(-1, self.navigationConfig.turnSpeed);
                V360Util.addEvent(document, "mouseup", function () {
                    self.stopAutoRotate();
                }, false);
                V360Util.addEvent(document, "touchend", function () {
                    self.stopAutoRotate();
                }, false);
            }
            V360Util.addEvent(turnLeftBtn, "mousedown", this.navigationTurnLeftMouseDownHandlerListener, false);
            V360Util.addEvent(turnLeftBtn, "touchstart", this.navigationTurnLeftMouseDownHandlerListener, false);
        } else {
            turnLeftBtn.style.display = "none";
        }

        //TURN RIGHT
        var turnRightBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationTurnRight");
        if (this.navigationConfig.showTurn && this.navigationConfig.showButtons) {

            this.navigationTurnLeftMouseDownHandlerListener = function (event) {

                self.startAutoRotate(1, self.navigationConfig.turnSpeed);
                V360Util.addEvent(document, "mouseup", function () {
                    self.stopAutoRotate();
                }, false);
                V360Util.addEvent(document, "touchend", function () {
                    self.stopAutoRotate();
                }, false);
            }
            V360Util.addEvent(turnRightBtn, "mousedown", this.navigationTurnLeftMouseDownHandlerListener, false);
            V360Util.addEvent(turnRightBtn, "touchstart", this.navigationTurnLeftMouseDownHandlerListener, false);
        } else {
            turnRightBtn.style.display = "none";
        }

        //CLOSE LEFT
        var closeBtn = V360Util.getFirstElementByClassName(this.innerHolder, "View360-navigationClose");
     
        if(closeBtn){
            if (this.navigationConfig.showClose) {

                V360Util.addEvent(closeBtn, "click", function (event) {
                    if(self.isFullscreen) self.exitFullscreenBtnHandler();
                    else if(self.lightbox) self.lightbox.close();
        
                }, false);

            
            } else {
                closeBtn.style.display = "none";
            }
        }




        if (!this.navigationConfig.showFullscreen || !this.navigationConfig.showButtons) {
            V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenEnter").style.display = "none";
            V360Util.getFirstElementByClassName(self.innerHolder, "View360-navigationFullscreenExit").style.display = "none";
        }
    };

    this.zoomIn = function () {
        for (var i = 0; i < this.config.zoomMultipliers.length; i++) {
            if (this.config.zoomMultipliers[i] > this.currentZoom) {
                this.setZoom(this.config.zoomMultipliers[i]);
                break;
            }
        }
    };

    this.zoomOut = function () {
        for (var i = this.config.zoomMultipliers.length - 1; i >= 0; i--) {
            if (this.config.zoomMultipliers[i] < this.currentZoom) {
                this.setZoom(this.config.zoomMultipliers[i]);
                break;
            }
        }
    };



    this.setZoom = function (zoom, withoutRefresh) {

        this.currentZoom = zoom;

        if (!withoutRefresh)
            this.displayImageByIdx(this.latestImageIdx);

        this.zoomChanged = true;
        if (this.currentZoom == 1) {
            this.pauseFullSizeImageLoader();
        } else {
            if (this.config.loadFullSizeImagesOnZoom === false) {
            }
            else if (this.config.loadFullSizeImagesOnZoom === true) {
                this.startFullSizeImageLoader();
            } else if (this.currentZoom >= this.config.loadFullSizeImagesOnZoom) {
                this.startFullSizeImageLoader();
            }
        }
    }

    this.startFullSizeImageLoader = function () {

        if (!this.config.fullSizeImagesDirectory || this.config.fullSizeImagesDirectory == "") {
            return;
        }

        var self = this;

        if (!this.fullSizeLoaderInfo) {
            this.fullSizeLoaderInfo = new this.fullSizeLoaderInfoClass(V360Util.getFirstElementByClassName(this.innerHolder, "View360-fullSizeLoaderHolder"));
            this.fullSizeLoaderInfo.setConfig(this.fullSizeLoaderInfoConfig);

            var fullSizeLoader = new View360Loader();

            var callbackProgress = function (progressObj) {
                self.fullSizeLoaderInfo.setPercent(progressObj.percent);
            };

            var callbackOne = function (_image, isError, images) {
            };

            var callbackEach = function (_image, isError) {

                //if (_image == self.fullSizeImages[self.latestImageIdx]) {
                if (_image == self._getFullSizeImageByIdx(self.latestImageIdx)) {

                    self.displayCurrentImage();
                }

            };

            var callbackAll = function (_images, errorCount) {
                self.fullSizeLoaderInfo.hide();
                self.fullSizeImages = _images;
                self.displayCurrentImage();
            };


            fullSizeLoader.setCallbackOne(callbackOne);
            fullSizeLoader.setCallbackAll(callbackAll);
            fullSizeLoader.setCallbackEach(callbackEach);
            fullSizeLoader.setCallbackProgress(callbackProgress);
            fullSizeLoader.setSources(this.sources);
            fullSizeLoader.setDirectory(this.config.fullSizeImagesDirectory);
            fullSizeLoader.start();

            self.fullSizeImages = fullSizeLoader.getImages();

            this.fullSizeLoaderInfo.show();
        }

    };

    this.pauseFullSizeImageLoader = function () {

    };

    this.unbindEvents = function () {

    };

    this.autoRotateInterval = null;
    this.startAutoRotate = function (direction, turnSpeed, isPlayBtn) {

        this._isPlayBtn = isPlayBtn;
        this._oneLoopCount = 0;

        turnSpeed = turnSpeed ? turnSpeed : this.config.autoRotateSpeed;

        this.stopAutoRotate();
        var self = this;
        this.autoRotateInterval = setInterval(function () {
            if (!self.autoRotatePaused)
                self.autoRotateFunc(direction);
        }, turnSpeed);


    };

    this.stopAutoRotate = function () {
        clearInterval(this.autoRotateInterval);
    };


    this.autoCreateImages = function () {

        if (!this.config.autoLoadImages)
            return;

        var p = this.config.imagesPattern;

        this.config.images = new Array();
        this.sources = new Array();
        for (var r = 0; r < this.config.rows; r++) {
            this.config.images[r] = new Array();
            for (var c = 0; c < this.config.columns; c++) {
                if (!this.config.images[c]) {
                    this.config.images[c] = new Array();
                }

                var __col = c;
                if (this.config.opositeDirection === true) __col = this.config.columns - c - 1;
                var __col = __col + this.config.imagesNumbering;


                var __row = r;
                if (this.config.opositeDirectionUpDown === true) __row = this.config.rows - r - 1;
                var __row = __row + this.config.imagesNumberingUpDown;

                var name = p.replace("%ROW", __row).replace("%COL", __col);

                var name = name.replace("%R%", V360Util.pad(__row, 1)).replace("%C%", V360Util.pad(__col, 1));
                var name = name.replace("%RR%", V360Util.pad(__row, 2)).replace("%CC%", V360Util.pad(__col, 2));
                var name = name.replace("%RRR%", V360Util.pad(__row, 3)).replace("%CCC%", V360Util.pad(__col, 3));

                this.config.images[c].push(name);

                this.sources.push(name);
            }
        }

    };
        
    this.calculate = function () {

        this.rows = 0;
        this.cols = 0;
        if (this.config.images instanceof Array) {
            if (this.config.images[0] && this.config.images[0] instanceof Array) {
                this.rows = this.config.images[0].length;
                this.cols = this.config.images.length;
            } else {
                this.rows = this.config.images.length;
                this.cols = 1;
            }
        }

        this.totalImages = this.rows * this.cols;

    };

    this.responsiveResize = function () {

        if(this.disableResponsive) return;
       
        if (this.responsiveConst) {
            var w = this.holder.offsetWidth;
            if (w == this.latestRWidth) {
                return;
            }

            var h = Math.round(w * this.responsiveConst);
            this.holder.style.height = h + "px";
            this.config.width = w;
            this.config.height = h;
            this.latestRWidth = w;
            this.updateCanvasSize();
            this.displayCurrentImage();
        }
    };

    this.responsiveFitResize = function () {
        
                if(this.disableResponsive) return;
       

        //if (this.responsiveConst) {
        var w = this.holder.offsetWidth;
        var h = this.holder.offsetHeight;

        if (w == this.latestRWidth && h == this.latestRHeight) {
            return;
        }

        //var h = Math.round(w * this.responsiveConst);
        //this.holder.style.height = h + "px";
        this.config.width = w;
        this.config.height = h;
        this.latestRWidth = w;
        this.latestRHeight = h;
        this.updateCanvasSize();
        this.displayCurrentImage();
        //}
    };

    this.resizeResponsiveByImage = function (imgObj) {
        var w = this.holder.offsetWidth;
        this.responsiveConst = (imgObj.height / imgObj.width);
        var h = Math.round(w * this.responsiveConst);
        this.holder.style.height = h + "px";
        this.config.height = h;
        this.updateCanvasSize();
    };

    this.resizeResponsiveFitByImage = function (imgObj) {
        var w = this.holder.offsetWidth;
        var h = this.holder.offsetHeight;

        // this.responsiveConst = (imgObj.height / imgObj.width);
        // var h = Math.round(w * this.responsiveConst);
        //this.holder.style.height = h + "px";
        //this.config.height = h;
        //this.config.height = w;
        this.updateCanvasSize();
    };

    this.updateCanvasSize = function () {


        V360Util.setAttribute(this.canvas, "width", this.config.width);
        V360Util.setAttribute(this.canvas, "height", this.config.height);

        this.canvasHolder.style.width = this.config.width + "px";
        this.canvasHolder.style.height = this.config.height + "px";

        this.innerHolder.style.width = this.config.width + "px";
        this.innerHolder.style.height = this.config.height + "px";

        if (this.latestImageIdx !== undefined) {
            this.displayImageByIdx(this.latestImageIdx);
        } else if (this.noOneImage !== undefined) {
            this.displayImage(this.noOneImage, this.config.firstImageOpacity);
        }

    };

    this.lightboxCloseHandler = function(){
        this.destroy();
    }

    this.responsiveInterval = null;
    this.currentZoom = 1;
    this.start = function (holder) {

        if(this.config.loaderShow){

            this.loaderInfoClass = View360LoadingInfo_New;
            this.fullSizeLoaderInfoClass = View360LoadingInfo_New;

            this.loaderInfoConfig.loaderShow = this.config.loaderShow;
            this.loaderInfoConfig.loaderColor = this.config.loaderColor;
            this.loaderInfoConfig.loaderClassname = this.config.loaderClassname;

        }
        
        var self = this;

        this.holder = holder;

        if (this.config.mode == "lightbox-responsive" || this.config.mode == "lightbox-css") {
            this.lightbox = new View360Lightbox();
            this.lightbox.closeCallback = this.lightboxCloseHandler.bind(this);
            this.lightbox.setConfig(this.config);

            this.holder = document.createElement("div");
            this.lightbox.setContent(this.holder);
            this.lightbox.open();
        }

        if (this.config.mode == "lightbox") {

            this.lightbox = new View360Lightbox();
            this.lightbox.closeCallback = this.lightboxCloseHandler.bind(this);
            this.lightbox.setConfig(this.config);
            this.holder = document.createElement("div");
            this.lightbox.setContent(this.holder);
            this.lightbox.open();

        } else if (this.config.mode == "fit") {

            this.config.width = this.holder.offsetWidth;
            this.config.height = this.holder.offsetHeight;

        } else if (this.config.mode == "fullscreen") {

            this.holder = document.createElement("div");
            this.holder.style.position = "fixed";
            this.holder.top = "0px";
            this.holder.left = "0px";

            document.body.appendChild(this.holder);
            this.config.width = window.innerWidth;
            this.config.height = window.innerHeight;

        } else if (this.config.mode == "responsive" || this.config.mode == "responsive-width" ) {

            clearInterval(this.responsiveInterval);
            //var self = this;
            this.responsiveInterval = setInterval(function () {
                self.responsiveResize();
            }, 500);

            this.config.width = this.holder.offsetWidth;
            this.config.height = this.holder.offsetHeight;

        } else if (this.config.mode == "responsive-fit" || this.config.mode == "lightbox-responsive"  || this.config.mode == "lightbox-css") {

            this.holder.style.top = "0px";
            this.holder.style.left = "0px";
            this.holder.style.bottom = "0px";
            this.holder.style.right = "0px";
            this.holder.style.position = "absolute";
            this.holder.style.overflow = "hidden";

            clearInterval(this.responsiveInterval);
            this.responsiveInterval = setInterval(function () {
                self.config.width = self.holder.offsetWidth;
                self.config.height = self.holder.offsetHeight;
                self.responsiveFitResize();
            }, 500);

            this.config.width = this.holder.offsetWidth;
            this.config.height = this.holder.offsetHeight;

        } else if (this.config.mode == "fixed") {
            //this.holder.style.width = this.config.width + "px";
            //this.holder.style.height = this.config.height + "px";
        }


        setTimeout(function () {
            self._timeout = true;
        }, 29 * 1000);


        this.currentZoom = 1;
        this.panMoveX = 0;
        this.panMoveY = 0;

        this.autoCreateImages();
        this.calculate();
        this.addInterface();
        this.bindEvents();


        self.smallLoaded = false;

        this.loaderInfo = new this.loaderInfoClass(V360Util.getFirstElementByClassName(this.innerHolder, "View360-loaderHolder"));
        this.loaderInfo.setConfig(this.loaderInfoConfig);
        this.loaderInfo.show();

        var sources = this.sources; // [].concat.apply( this.config.images )

        var canvas = this.canvas = V360Util.getFirstElementByClassName(this.innerHolder, "View360-canvas");
        var canvasHolder = this.canvasHolder = V360Util.getFirstElementByClassName(this.innerHolder, "View360-canvasHolder");

        self.updateCanvasSize();

        this.ctx = canvas.getContext("2d");

        var loader = new View360Loader();

        var callbackProgress = function (progressObj) {
            self.loaderInfo.setPercent(progressObj.percent);
        };

        var callbackOne = function (_image, isError) {

            if (!isError) {
                if (self.config.mode == "responsive" || self.config.mode == "responsive-width") {
                    self.resizeResponsiveByImage(_image);
                } else if (self.config.mode == "responsive-fit") {
                    self.resizeResponsiveFitByImage(_image);
                }
                self.displayImage(_image, 0.5);
                self.noOneImage = _image;

            }

        };

        var callbackAll = function (_images, errorCount) {

            self.smallLoaded = true;

            self.loaderInfo.hide();


            if (self.config.showTurnMeOn) {
                self.turnMeOnHolder.style.display = "block";
                //self.turnMeOnHolder.style.opacity = "1";
            }



            if (self.navigationConfig.showButtons) {
                //
                self.navigationHolder.style.visibility = "visible";
            } else {
                self.navigationHolder.style.display = "none";
            }

            self.images = _images;
            self.displayImageByIdx(self.config.initialImage);
            self.currentCol = self.config.initialImage;

            if (self.config.autoRotate)
                self.startAutoRotate();
            else if (self.config.oneTurnOnStartUp)
                self.startInertiaAutoMoveInterval(true);

        }

        loader.setCallbackOne(callbackOne, sources[this.config.initialImage]);
        loader.setCallbackAll(callbackAll);
        loader.setCallbackProgress(callbackProgress);
        loader.setSources(sources);
        loader.setDirectory(this.config.imagesDirectory);







        if (this.config.loadOnPageLoad) {

            if (this.config.loadFirstImage) loader.loadOne();

            if (document.readyState === 'complete') {
                loader.start();
            }
            else {
                var interval = setInterval(function () {
                    if (document.readyState === 'complete') {
                        clearInterval(interval);
                        if (!self.config.loadFirstImage) loader.loadOne();
                        loader.start();
                    }
                }, 200);
            }

        } else {
            loader.loadOne();
            loader.start();
        }

    };
    this.setConfig = function (config) {
        for (var name in config) {
            this.config[name] = config[name];
        }
    };
    this.setNavigationConfig = function (navigationConfig) {
        for (var name in navigationConfig) {
            this.navigationConfig[name] = navigationConfig[name];
        }
    };
    this.setProperty = function (name, prop) {
        this.config[name] = prop;
    };

    this.setMode = function (mode) {
        this.setProperty("mode", mode);
    };

    //v1.3.0
    this.setFitMode = function (fitmode) {
        this.setProperty("fitmode", fitmode);
    };
    this.setImagesPattern = function (imagesPattern) {
        this.setProperty("imagesPattern", imagesPattern);
    };

    this.setImagesDirectory = function (imagesDirectory) {
        this.setProperty("imagesDirectory", imagesDirectory);
    };

    this.setFullSizeImagesDirectory = function (fullSizeImagesDirectory) {
        this.setProperty("fullSizeImagesDirectory", fullSizeImagesDirectory);
    };

    this.setHtmlTemplate = function (htmlTemplate) {
        this.htmlTemplate = htmlTemplate;
    };

    this.setLoaderInfoClass = function (loaderInfoClass) {
        this.loaderInfoClass = loaderInfoClass;
    };

    this.setLoaderInfoConfig = function (loaderInfoConfig) {
        this.loaderInfoConfig = loaderInfoConfig;
    };

    this.setFullSizeLoaderInfoClass = function (fullSizeLoaderInfoClass) {
        this.fullSizeLoaderInfoClass = fullSizeLoaderInfoClass;
    };

    this.setFullSizeLoaderInfoConfig = function (fullSizeLoaderInfoConfig) {
        this.fullSizeLoaderInfoConfig = fullSizeLoaderInfoConfig;
    };

    this.loadPreset = function (path) {
        var self = this;
        V360Util.loadJSON(path, function (jsonObj) {
            self.loaderInfoClass = View360LoadingInfo_New;
            self.fullSizeLoaderInfoClass = View360LoadingInfo_New;

            self.loaderInfoConfig.loaderShow = jsonObj.v360config.loaderShow;
            self.loaderInfoConfig.loaderColor = jsonObj.v360config.loaderColor;
            self.loaderInfoConfig.loaderClassname = jsonObj.v360config.loaderClassname;

            self.setConfig(jsonObj.v360config);
            self.setNavigationConfig(jsonObj.v360navigation);
            self.setImagesDirectory((self.consObj.basepath ? self.consObj.basepath + "/" : "") + self.consObj.product);
            self.start(V360Util.getElementById(self.consObj.id));
        },
            function (jsonObj) { console.log("View360: Load preset failed.") }
        );
    };

    if (this.consObj && this.consObj.preset) {
        this.loadPreset((this.consObj.basepath ? this.consObj.basepath + "/" : "") + this.consObj.preset);
    }

};


function View360UtilStatic() {

    this.getElementById = function (id) {
        return document.getElementById(id);
    };
    this.getFirstElementByClassName = function (parent, className) {
        var els = this.getElementsByClassName(parent, className);
        return els[0];
    };
    this.getElementsByClassName = function (parent, className) {
        if (parent.getElementsByClassName)
            return parent.getElementsByClassName(className);
        else if (parent.querySelectorAll) {
            return parent.querySelectorAll("." + className);
        } else if (document.querySelectorAll) {
            return document.querySelectorAll("." + className);
        }
    };
    this.addEvent = function (el, e, f, c) {
        if (el.addEventListener) {
            return el.addEventListener(e, f, c);
        }
        else if (el.attachEvent) {
            return el.attachEvent('on' + e, f);
        }
    };
    this.removeEvent = function (el, e, f) {
        if (el.addEventListener) {
            return el.removeEventListener(e, f);
        }
        else if (el.detachEvent) {
            return el.detachEvent('on' + e, f);
        }
    };

    this.setAttribute = function (el, n, v) {
        if (el.setAttribute) {
            el.setAttribute(n, v);
        } else {
            el[n] = v;
        }
    };


    this.hasClass = function (ele, cls) {
        return ele.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
    };

    this.addClass = function (ele, cls) {
        if (!this.hasClass(ele, cls)) ele.className += " " + cls;
    };

    this.removeClass = function (ele, cls) {
        if (this.hasClass(ele, cls)) {
            var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
            ele.className = ele.className.replace(reg, ' ');
        }
    };


    this.pad = function (num, size) {
        var s = num + "";
        while (s.length < size) s = "0" + s;
        return s;
    };


    this.loadJSON = function (path, success, error) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    if (success)
                        success(JSON.parse(xhr.responseText));
                } else {
                    if (error)
                        error(xhr);
                }
            }
        };
        xhr.open("GET", path, true);
        xhr.send();
    };

};

V360Util = new View360UtilStatic();


function View360Loader(sources) {

    this.sources = sources;
    this.directory;

    this.callbackOne = function () {
    };
    this.callbackAll = function () {
    };
    this.callbackEach = function () {
    };
    this.callbackOn = {};

    this.images = [];
    this.totalImages;

    this.setDirectory = function (directory) {
        this.directory = directory;
    };
    this.setSources = function (sources) {
        this.sources = sources;
    };
    this.setCallbackOne = function (callbackOne, urlOne) {
        this.callbackOne = callbackOne;
        this.urlOne = urlOne;
    };
    this.setCallbackAll = function (callbackAll) {
        this.callbackAll = callbackAll;
    };
    this.setCallbackEach = function (callbackEach) {
        this.callbackEach = callbackEach;
    };
    this.setCallbackProgress = function (callbackProgress) {
        this.callbackProgress = callbackProgress;
    };
    this.setCallbackOn = function (no, callbackOn) {
        this.callbackOn[no] = callbackOn;
    };

    this.loadFirstNum = function (num) {
        this.loadFirstNum = num;
    };

    this.start = function () {

        var self = this;

        this.loadImages(0,
            this.sources.length - 1,
            function (image, error) {
                self.callbackOne(image, error);
            }, function (images, errorCount) {
                self.callbackAll(images, errorCount);
            }, function (progressObj) {
                self.callbackProgress(progressObj);
            }, function (image, error) {
                self.callbackEach(image, error);
            });

    };

    this.getImages = function () {
        return this.images;
    };

    this.loadOne = function () {
        var self = this;
        var img = new Image();
        img.onload = function (e) {
            self.callbackOne(e.currentTarget, false);
        };
        img.src = this.directory + "/" + this.urlOne;
    };

    this.loadImages = function (from, to, _callbackOne, _callbackAll, _callbackProgress, _callbackEach) {

        var self = this;

        var loadedImages = 0;
        var numImages = 0;
        var errorCount = 0;

        // get num of sources
        var numImages = to - from + 1;

        for (var i = from; i <= to; i++) {

            this.images[i] = new Image();
            this.images[i].onload = function (e) {
                loadedImages++;
                /* if (loadedImages == 1) {
                     _callbackOne(e.currentTarget, false);
                 }*/

                if (loadedImages >= numImages) {
                    _callbackAll(self.images, errorCount);
                }

                _callbackEach(e.currentTarget, false);

                var percent = Math.ceil((loadedImages / numImages) * 100);
                _callbackProgress({
                    loaded: loadedImages,
                    total: numImages,
                    error: errorCount,
                    percent: percent
                });

            };
            this.images[i].onerror = function (e) {

                errorCount++;

                /*if (loadedImages == 0) {
                    _callbackOne(e.currentTarget, true);
                }*/
                if (++loadedImages >= numImages) {
                    _callbackAll(self.images, errorCount);
                }

                _callbackEach(e.currentTarget, true);

                var percent = Math.ceil((loadedImages / numImages) * 100);
                _callbackProgress({
                    loaded: loadedImages,
                    total: numImages,
                    error: errorCount,
                    percent: percent
                });

            };

            this.images[i].src = this.directory + "/" + this.sources[i];

        }


    };

};






var View360LoadingInfo_New = function (holder) {

    this.holder = holder;

    this.config = {
        loaderShow: true,
        loaderColor: "#dddddd",
        loaderClassname: "ball-pulse",
    }


    this.getHTMLTemplate = function () {
        var htmlTemplate = ' ';

        htmlTemplate += '';
        
        htmlTemplate += '    <style>';
        htmlTemplate += '        .View360-loaderInnerHolder > .load-container > .loader > div > div{';
        htmlTemplate += '            background: ' + this.config.loaderColor +';';
        htmlTemplate += '        }';
        htmlTemplate += '        .View360-loaderInnerHolder > .load-container > .loader{';
        htmlTemplate += '            margin-left: -50%;';
         htmlTemplate += '           margin-top: -50%;';
        htmlTemplate += '        }';

        htmlTemplate += '        .View360-loaderInnerHolder > .load-container  {';
        htmlTemplate += '            top: 50%;';
        htmlTemplate += '            position: absolute;';
        htmlTemplate += '            left: 50%;';
        htmlTemplate += '        }';

        htmlTemplate += '    </style>';
            
        
        htmlTemplate += '            <div class="View360-loaderInnerHolder">  ';
        htmlTemplate += '            <div class="View360-loaderBackground"></div>  ';
        htmlTemplate += '                    <div class="loaders load-container">';
                        
        var cn = this.config.loaderClassname;
        if (cn == "ball-pulse") htmlTemplate += '<div class="loader"><div class="ball-pulse"><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-grid-pulse") htmlTemplate += '<div class="loader"><div class="ball-grid-pulse"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "square-spin") htmlTemplate += '<div class="loader"><div class="square-spin"><div></div></div></div>';
        else if (cn == "ball-pulse-rise") htmlTemplate += '<div class="loader"><div class="ball-pulse-rise"><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-rotate") htmlTemplate += '<div class="loader"><div class="ball-rotate"><div></div></div></div>';
        else if (cn == "cube-transition") htmlTemplate += '<div class="loader"><div class="cube-transition"><div></div><div></div></div></div>';
        else if (cn == "ball-zig-zag") htmlTemplate += '<div class="loader"><div class="ball-zig-zag"><div></div><div></div></div></div>';
        else if (cn == "ball-zig-zag-deflect") htmlTemplate += '<div class="loader"><div class="ball-zig-zag-deflect"><div></div><div></div></div></div>';
        else if (cn == "ball-triangle-path") htmlTemplate += '<div class="loader"><div class="ball-triangle-path"><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-scale") htmlTemplate += '<div class="loader"><div class="ball-scale"><div></div></div></div>';
        else if (cn == "line-scale") htmlTemplate += '<div class="loader"><div class="line-scale"><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "line-scale-party") htmlTemplate += '<div class="loader"><div class="line-scale-party"><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-scale-multiple") htmlTemplate += '<div class="loader"><div class="ball-scale-multiple"><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-pulse-sync") htmlTemplate += '<div class="loader"><div class="ball-pulse-sync"><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-beat") htmlTemplate += '<div class="loader"><div class="ball-beat"><div></div><div></div><div></div></div></div>';
        else if (cn == "line-scale-pulse-out") htmlTemplate += '<div class="loader"><div class="line-scale-pulse-out"><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "line-scale-pulse-out-rapid") htmlTemplate += '<div class="loader"><div class="line-scale-pulse-out-rapid"><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-scale-ripple") htmlTemplate += '<div class="loader"><div class="ball-scale-ripple"><div></div></div></div>';
        else if (cn == "ball-scale-ripple-multiple") htmlTemplate += '<div class="loader"><div class="ball-scale-ripple-multiple"><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-spin-fade-loader") htmlTemplate += '<div class="loader"><div class="ball-spin-fade-loader"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "line-spin-fade-loader") htmlTemplate += '<div class="loader"><div class="line-spin-fade-loader"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
        else if (cn == "ball-grid-beat") htmlTemplate += '<div class="loader"><div class="ball-grid-beat"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
        htmlTemplate += '</div>';

        htmlTemplate += '    </div>  ';
        htmlTemplate += '    ';

        return htmlTemplate;
    }




    this.initializeInterface = function () {

        this.holder.innerHTML = this.getHTMLTemplate();

    }

    this.destroyInterface = function () {

        //this.loaderInnerHolder = null;
        this.holder.innerHTML = "";

    }

    this.setPercent = function (percent) {


    }

    this.setConfig = function (config) {
        for (var name in config) {
            this.config[name] = config[name];
        }
    }
    this.show = function () {
        if (!this.config.loaderShow)
            return;
        this.initializeInterface();
    }
    this.hide = function () {
        if (!this.config.loaderShow)
             return;
         this.destroyInterface();
    }

    this.drawPercent = function (percent) {
    }

}




var View360LoadingInfo = function (holder) {

    this.holder = holder;

    this.config = {
        display: true,
        holderClassName: null,
        loadingTitle: null,
        loadingSubtitle: null,
        loadingMessage: null,
        modalBackground: "#FFF",
        modalOpacity: 0.5,
        circleWidth: "70",
        circleLineWidth: "10",
        circleLineColor: "#555",
        circleBackgroundColor: "#FFF"
    };

    this.htmlTemplate = ' ';
    this.htmlTemplate += '    <div class="View360-loaderInnerHolder">  ';
    this.htmlTemplate += '        <div class="View360-loaderBackground"></div>  ';
    this.htmlTemplate += '        <div class="View360-loader">  ';
    this.htmlTemplate += '             <div class="View360-loadingTitle"></div>  ';
    this.htmlTemplate += '             <div class="View360-loadingSubtitle"></div>  ';
    this.htmlTemplate += '             <div class="View360-loadingMessage"></div>  ';
    this.htmlTemplate += '             <div class="View360-visual">  ';
    this.htmlTemplate += '                 <div class="View360-visualHolder">  ';
    this.htmlTemplate += '                    <canvas class="View360-visualCanvas" width="80" height="80"></canvas>  ';
    this.htmlTemplate += '                    <div class="View360-percentHolder">  ';
    this.htmlTemplate += '                        <div class="View360-percent"></div>  ';
    this.htmlTemplate += '                    </div>  ';
    this.htmlTemplate += '                </div>  ';
    this.htmlTemplate += '             </div>  ';
    this.htmlTemplate += '        </div>  ';
    this.htmlTemplate += '    </div>  ';
    this.htmlTemplate += '    ';


    this.setHtmlByClassOrHide = function (className, html) {
        var el = V360Util.getFirstElementByClassName(this.holder, className);
        if (!el)
            return;
        if (!html || html == "") {
            el.style.display = "none";
        } else {
            el.innerHTML = html;
        }
    };

    this.initializeInterface = function () {

        this.holder.innerHTML = this.htmlTemplate;
        if (this.config.holderClassName)
            this.holder.className += " " + this.config.holderClassName;
        this.setHtmlByClassOrHide("View360-loadingTitle", this.config.loadingTitle);
        this.setHtmlByClassOrHide("View360-loadingSubtitle", this.config.loadingSubtitle);
        this.setHtmlByClassOrHide("View360-loadingMessage", this.config.loadingMessage);


        this.canvas = V360Util.getFirstElementByClassName(this.holder, "View360-visualCanvas");

        V360Util.setAttribute(this.canvas, "width", this.config.circleWidth);
        V360Util.setAttribute(this.canvas, "height", this.config.circleWidth);

        this.percentHolder = V360Util.getFirstElementByClassName(this.holder, "View360-percentHolder");
        this.percentHolder.style.width = this.config.circleWidth + "px";
        this.percentHolder.style.height = this.config.circleWidth + "px";
        this.percent = V360Util.getFirstElementByClassName(this.holder, "View360-percent");
        this.percent.style.width = this.config.circleWidth + "px";
        this.percent.style.height = this.config.circleWidth + "px";

        this.loaderInnerHolder = V360Util.getFirstElementByClassName(this.holder, "View360-loaderBackground");
        this.loaderInnerHolder.style.background = this.config.modalBackground;
        this.loaderInnerHolder.style.opacity = this.config.modalOpacity;

        this.percentEl = V360Util.getFirstElementByClassName(this.holder, "View360-percent");

    };

    this.destroyInterface = function () {

        this.percentEl = null;
        this.loaderInnerHolder = null;
        this.holder.innerHTML = "";
    };

    this.setPercent = function (percent) {

        if (!this.config.display)
            return;

        if (!this.percentEl)
            return;
        this.drawPercent(percent);
    };

    this.setConfig = function (config) {
        for (var name in config) {
            this.config[name] = config[name];
        }
    };
    this.show = function () {
        if (!this.config.display)
            return;
        this.initializeInterface();
    };
    this.hide = function () {
        if (!this.config.display)
            return;
        this.destroyInterface();
    };

    this.drawPercent = function (percent) {
        try {

            this.percentEl.innerHTML = percent + "%";

            var current = percent / 100;

            var ctx = this.canvas.getContext('2d');
            var x = this.canvas.width / 2;
            var y = this.canvas.height / 2;


            var radius = (this.canvas.width - this.config.circleLineWidth) / 2;

            var circ = Math.PI * 2;
            var quart = Math.PI / 2;

            ctx.lineWidth = this.config.circleLineWidth;
            ctx.strokeStyle = this.config.circleLineColor;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;
            ctx.shadowColor = '#656565';
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            ctx.arc(x, y, Math.max(0, radius - this.config.circleLineWidth / 2), 0, 2 * Math.PI, false);
            ctx.fillStyle = this.config.circleBackgroundColor;
            ctx.fill();
            ctx.beginPath();
            ctx.arc(x, y, Math.max(0, radius), -(quart), ((circ) * current) - quart, false);
            ctx.stroke();
        } catch (ex) {

        }

    };

};


var View360Lightbox = function () {

    this.config = {
        width: "800",
        height: "600",
        paddingTop: "10px",
        paddingBottom: "10px",
        paddingLeft: "10px",
        paddingRight: "10px",
        mode: "fixed"
    };

    this.setConfig = function (config) {
        for (var name in config) {
            this.config[name] = config[name];
        }
    };

    this.open = function () {
        this.addInterface();
    };
    this.close = function () {
        this.removeInterface();
    };

    this.addInterface = function () {

        this.modalBackground = document.createElement("div");
        V360Util.setAttribute(this.modalBackground, "id", "View360-modalBackground");
        document.body.appendChild(this.modalBackground);

        this.lighboxOuter = document.createElement("div");
        this.lighboxOuter.style.top = window.pageYOffset + "px";

        V360Util.setAttribute(this.lighboxOuter, "id", "View360-lighboxOuter");
        document.body.appendChild(this.lighboxOuter);

        this.lighbox = document.createElement("div");
        V360Util.setAttribute(this.lighbox, "id", "View360-lighbox");
        this.lighboxOuter.appendChild(this.lighbox);


        if(this.config.mode=="lightbox-responsive"){

            this.lighbox.style.maxWidth = this.config.width + "px";
            this.lighbox.style.maxHeight = this.config.height + "px";

            this.lighbox.style.top = this.config.paddingTop;
            this.lighbox.style.left = this.config.paddingLeft;
            this.lighbox.style.bottom = this.config.paddingBottom;
            this.lighbox.style.right = this.config.paddingRight;

        }else if(this.config.mode=="lightbox-css"){

        }else{
            this.lighbox.style.width = this.config.width + "px";
            this.lighbox.style.height = this.config.height + "px";
        }

         






        this.lighbox.appendChild(this.content);

        //events
        var self = this;
        this.modalClickHandlerListener = function (evt) {

            if (evt.target.getAttribute("id") != "View360-lighboxOuter" && evt.target.getAttribute("id") != "View360-modalBackground")
                return;

            self.close();

        }
        V360Util.addEvent(this.modalBackground, "mousedown", this.modalClickHandlerListener, false);
        V360Util.addEvent(this.lighboxOuter, "mousedown", this.modalClickHandlerListener, false);

    };

    this.setContent = function (content) {
        this.content = content;
    }

    this.removeInterface = function () {
        V360Util.removeEvent(this.lighboxOuter, "mousedown", this.modalClickHandlerListener);
        this.removeElement(this.lighbox);
        this.removeElement(this.lighboxOuter);
        this.removeElement(this.modalBackground);
        this.lighbox = null;
        this.lighboxOuter = null;
        this.modalBackground = null;
        if(this.closeCallback) this.closeCallback();
        this.closeCallback = null;
    };
    this.removeElement = function (element) {
        element && element.parentNode && element.parentNode.removeChild(element);
    };
};