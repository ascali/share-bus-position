(function() {
    // save these original methods before they are overwritten
    var proto_initIcon = L.Marker.prototype._initIcon;
    var proto_setPos = L.Marker.prototype._setPos;

    var oldIE = (L.DomUtil.TRANSFORM === 'msTransform');

    L.Marker.addInitHook(function () {
        var iconOptions = this.options.icon && this.options.icon.options;
        var iconAnchor = iconOptions && this.options.icon.options.iconAnchor;
        if (iconAnchor) {
            iconAnchor = (iconAnchor[0] + 'px ' + iconAnchor[1] + 'px');
        }
        this.options.rotationOrigin = this.options.rotationOrigin || iconAnchor || 'center bottom' ;
        this.options.rotationAngle = this.options.rotationAngle || 0;

        // Ensure marker keeps rotated during dragging
        this.on('drag', function(e) { e.target._applyRotation(); });
    });

    L.Marker.include({
        _initIcon: function() {
            proto_initIcon.call(this);
        },

        _setPos: function (pos) {
            proto_setPos.call(this, pos);
            this._applyRotation();
        },

        _applyRotation: function () {
            if(this.options.rotationAngle) {
                this._icon.style[L.DomUtil.TRANSFORM+'Origin'] = this.options.rotationOrigin;

                if(oldIE) {
                    // for IE 9, use the 2D rotation
                    this._icon.style[L.DomUtil.TRANSFORM] = 'rotate(' + this.options.rotationAngle + 'deg)';
                } else {
                    // for modern browsers, prefer the 3D accelerated version
                    this._icon.style[L.DomUtil.TRANSFORM] += ' rotateZ(' + this.options.rotationAngle + 'deg)';
                }
            }
        },

        setRotationAngle: function(angle) {
            this.options.rotationAngle = angle;
            this.update();
            return this;
        },

        setRotationOrigin: function(origin) {
            this.options.rotationOrigin = origin;
            this.update();
            return this;
        }
    });
})();

// Save original method before overwriting it below.
const _setPosOriginal = L.Marker.prototype._setPos

L.Marker.addInitHook(function() {
  const anchor = this.options.icon.options.iconAnchor
  this.options.rotationOrigin = anchor ? `${anchor[0]}px ${anchor[1]}px` : 'center center'
  // Ensure marker remains rotated during dragging.
  this.on('drag', data => { this._rotate() })
})

L.Marker.include({
  // _setPos is alled when update() is called, e.g. on setLatLng()
  _setPos: function(pos) {
    _setPosOriginal.call(this, pos)
    if (this.options.rotation) this._rotate()
  },
  _rotate: function() {
    this._icon.style[`${L.DomUtil.TRANSFORM}Origin`] = this.options.rotationOrigin
    this._icon.style[L.DomUtil.TRANSFORM] += ` rotate(${this.options.rotation}deg)`
  }
})