(function($) {
  /* jQuery form remember state plugin
     Name: rememberState
     Version: 1.4.1
     Description: When called on a form element, localStorage is used to
     remember the values that have been input up to the point of either
     saving or unloading. (closing window, navigating away, etc.) If
     localStorage isn't available, nothing is bound or stored.
     The plugin looks for an element with a class of remember_state to show
     a note indicating there is stored data that can be repopulated by clicking
     on the anchor within the remember_state container. If the element doesn't
     exist, it is created and prepended to the form.
     Usage: $("form").rememberState("my_object_name");
     Notes: To trigger the deletion of a form's localStorage object from
     outside the plugin, trigger the reset_state event on the form element
     by using $("form").trigger("reset_state");
     To manually call restore state, call $("form").rememberState("restoreState");
  */
  if (!window.localStorage || !window.JSON) {
    if (console && console.log) {
      !window.localStorage && console.log("ERROR: you browser does not support" +
        " localStorage (use this polyfill https://gist.github.com/350433)");
      !window.JSON&& console.log("ERROR: you browser does not support" +
        " JSON (use this polyfill http://bestiejs.github.com/json3/)");
    }
    return $.fn.rememberState = function() { return this; };
  }

  var remember_state = {
    name: "rememberState",
    clearOnSubmit: true,
    ignore: null,
    use_ids: false,
    objName: false,
    restoreState: function(e) {
      var data = JSON.parse(localStorage.getItem(this.objName)),
          $f = this.$el,
          $e;
      for (var i in data) {
        $e = $f.find("[name=\"" + data[i].name + "\"]");
        if ($e.is(":radio")) {
          $e.filter("[value=\"" + data[i].value + "\"]").prop("checked", true);
        }
        else if ($e.is(":checkbox") && data[i].value) {
          $e.prop("checked", true);
        }
        else if ($e.is("select")) {
          $e.find("[value=\"" + data[i].value + "\"]").prop("selected", true);
        }
        else {
          $e.val(data[i].value);
        }
        $e.change();
      }
      e && e.preventDefault && e.preventDefault();
    },
    chooseStorageProp: function() {
      if (this.$el.length > 1) {
        if (console && console.warn) {
          console.warn("WARNING: Cannot process more than one form with the same" +
            " object. Attempting to use form IDs instead.");
        }
        this.objName = this.$el.attr("id");
      }
    },
    errorNoID: function() {
      if (console && console.log) {
        console.log("ERROR: No form ID or object name. Add an ID or pass" +
          " in an object name");
      }
    },
    saveState: function(e) {
      var instance = e.data.instance;
      var values = instance.$el.serializeArray();
      // jQuery doesn't currently support datetime-local inputs despite a
      // comment by dmethvin stating the contrary:
      // http://bugs.jquery.com/ticket/5667
      // Manually storing input type until jQuery is patched
      instance.$el.find("input[type='datetime-local']").each(function() {
        var $i = $(this);
        values.push({ name: $i.attr("name"), value: $i.val() });
      });
      values = instance.removeIgnored(values);
      values.length && internals.setObject(instance.objName, values);
    },
    save: function() {
      var instance = this;
      if (!this.saveState) {
        instance = this.data(remember_state.name);
      }
      instance.saveState({ data: { instance: instance } });
    },
    removeIgnored: function(values) {
      var ignore = this.ignore;
      if (!ignore) { return values; }
      $.each(values, function(i, input) {
        if ($.inArray(input.name, ignore) !== -1) {
          values[i] = false;
        }
      });
      values = $.grep(values, function(val) { return val; });
      return values;
    },
    setName: function() {
      this.objName = this.objName || this.$el.attr("id");
      if (!this.objName) { this.errorNoID(); }
    },
    bindResetEvents: function() {
      var instance = this;
      if (this.clearOnSubmit) {
        this.$el.bind("submit." + this.name, function() {
          $(this).trigger("reset_state");
          $(window).unbind("unload." + instance.name);
        });
      }

      this.$el.bind("reset_state." + this.name, function() {
        localStorage.removeItem(instance.objName);
      });
      this.$el.find(":reset").bind("click." + this.name, function() {
        $(this).closest("form").trigger("reset_state");
      });
    },
    destroy: function(destroy_local_storage) {
      var namespace = "." + this.name;
      this.$el.unbind(namespace).find(":reset").unbind(namespace);
      $(window).unbind(namespace);
      destroy_local_storage && localStorage.removeItem(this.objName);
    },
    init: function() {
      this.setName();

      if (!this.objName) { return; }

      this.bindResetEvents();

      $(window).bind("unload." + this.name, { instance: this }, this.saveState);
    }
  };

  var internals = {
    setObject: function(key, value) { localStorage[key] = JSON.stringify(value); },
    getObject: function(key) { return JSON.parse(localStorage[key]); },
    createPlugin: function(plugin) {
      $.fn[plugin.name] = function(opts) {
        var $els = this,
            method = $.isPlainObject(opts) || !opts ? "" : opts,
            args = arguments;
        if (method && plugin[method]) {
          $els.each(function(i) {
            plugin[method].apply($els.eq(i).data(plugin.name), Array.prototype.slice.call(args, 1));
          });
        }
        else if (!method) {
          $els.each(function(i) {
            var plugin_instance = $.extend(true, {
              $el: $els.eq(i)
            }, plugin, opts);
            $els.eq(i).data(plugin.name, plugin_instance);
            plugin_instance.init();
          });
        }
        else {
          $.error('Method ' +  method + ' does not exist on jQuery.' + plugin.name);
        }
        return $els;
      };
    }
  };

  internals.createPlugin(remember_state);
})(jQuery);
