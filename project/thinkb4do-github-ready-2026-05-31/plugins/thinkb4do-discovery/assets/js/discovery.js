(function(){
  'use strict';
  function ready(fn){
    if(document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn, {once:true});
  }
  ready(function(){
    var root = document.getElementById('tb4-discovery');
    if(!root) return;
    root.setAttribute('data-tb4-ready','1');
  });
})();
