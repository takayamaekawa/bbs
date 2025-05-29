'use strict';
{
  const
    // 保持対象要素CSSセレクター
    cssSelectors = {
      // checked系
      checked: [
        'input[type=checkbox]',
        'input[type=radio]',
      ].join(),
      // value系
      value: [
        'input[type=text]',
        'input[type=password]',
        'input[type=datetime-local]',
        'input[type=date]',
        'input[type=time]',
        'input[type=month]',
        'input[type=week]',
        'input[type=number]',
        'input[type=email]',
        'input[type=range]',
        'input[type=search]',
        'input[type=tel]',
        'input[type=url]',
        'textarea',
        // type属性を持たないinput要素(text扱い)
        'input:not([type])',
      ].join(),
      // selected系
      selected: 'select',
    },

    elements = {},
    storage = {
      key: location.pathname + ':inputElementRetention',
    };

  window.addEventListener('DOMContentLoaded', function() {
    if (checkStorage(storage.key + '_c') !== true) return;

    storage.data = sessionStorage.getItem(storage.key);
    storage.data = storage.data !== null ? JSON.parse(storage.data) : {};

    // 対象要素取得・イベントリスナー追加・storageデータを各要素へ反映
    // checked系
    elements.checked = document.querySelectorAll(cssSelectors.checked);
    for (let i = 0; i < elements.checked.length; i++) {
      const e = elements.checked[i];
      e.addEventListener('change', storageSet);
      if (storage.data.checked !== undefined) e.checked = storage.data.checked[i];
    }

    // value系
    elements.value = document.querySelectorAll(cssSelectors.value);
    for (let i = 0; i < elements.value.length; i++) {
      const e = elements.value[i];
      e.addEventListener('input', storageSet);
      if (storage.data.value !== undefined) e.value = storage.data.value[i];
    }

    // selected系
    elements.selected = document.querySelectorAll(cssSelectors.selected);
    for (let i = 0; i < elements.selected.length; i++) {
      const e = elements.selected[i];
      e.addEventListener('change', storageSet);
      if (storage.data.selected !== undefined) {
        for (let l = 0; l < storage.data.selected[i].length; l++) {
          e.options[l].selected = storage.data.selected[i][l];
        }
      }
    }
  });

  // 各要素の状態取得・storageに保持
  function storageSet() {
    // checked系
    storage.data.checked = [];
    for (let i = 0; i < elements.checked.length; i++) {
      const e = elements.checked[i];
      storage.data.checked[i] = e.checked ? 1 : 0;
    }

    // value系
    storage.data.value = [];
    for (let i = 0; i < elements.value.length; i++) {
      const e = elements.value[i];
      storage.data.value[i] = e.value;
    }

    // selected系
    storage.data.selected = [];
    for (let i = 0; i < elements.selected.length; i++) {
      const e = elements.selected[i];
      const selectedArray = [];
      for (let l = 0; l < e.options.length; l++) {
        selectedArray.push(e.options[l].selected ? 1 : 0);
      }

      storage.data.selected[i] = selectedArray;
    }

    sessionStorage.setItem(storage.key, JSON.stringify(storage.data));
  }

  function checkStorage(str) {
    try {
      sessionStorage.setItem(str, str);
      sessionStorage.removeItem(str);
      return true;
    } catch (e) {
      return false;
    }
  }
}
