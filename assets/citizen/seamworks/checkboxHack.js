/**
 * 万能的 Checkbox Hack。
 * 无需 jQuery 支持。
 *
 * @see https://github.com/wikimedia/Vector/blob/master/resources/skins.vector.js/checkbox.js
 * @type {string}
 */

!function (window, document) {
	"use strict";

	const $containerSelector	= '.p-menu-checkbox-container',
		  $checkboxSelector		= '.p-menu-checkbox',
		  $buttonSelector		= '.p-menu-checkbox-button',
		  $targetSelector		= '.p-menu-checkbox-target';

	function init() {
		const containers = document.querySelectorAll( $containerSelector );

		containers.forEach( ( container ) => {
			const checkbox = container.querySelector( $checkboxSelector ),
				  button = container.querySelector( $buttonSelector ),
				  target = container.querySelector( $targetSelector );

			if ( !( checkbox && button && target ) ) return;

			bindCheckbox( window, checkbox, button, target );
		} );
	}

	/**
	 * 修改按钮的 aria-expanded 状态以匹配选中状态。
	 *
	 * @param {HTMLInputElement} checkbox
	 * @param {HTMLElement} button
	 * @return {void}
	 * @ignore
	 */
	function updateAriaExpanded( checkbox, button ) {
		if ( button ) {
			button.setAttribute( 'aria-expanded', checkbox.checked.toString() );
			return;
		}

		checkbox.setAttribute( 'aria-expanded', checkbox.checked.toString() );
	}

	/**
	 * 配置选中状态并触发 input 事件。
	 * 对复选框进行编程更改。选中不会触发输入或更改事件。
	 * 输入事件将依次调用 updateAriaExpanded()。
	 *
	 * 复选框以外的某个元素发生用户事件时调用 setCheckedState() 将会导致复选框状态更改。
	 *
	 * https://html.spec.whatwg.org/multipage/indices.html#event-change
	 * 为了保持完整性，change 事件也应该被触发，但是此处不使用 change 事件，也不期望其被使用。
	 *
	 * @param {HTMLInputElement} checkbox
	 * @param {boolean} checked
	 * @return {void}
	 * @ignore
	 */
	function setCheckedState( checkbox, checked ) {
		/** @type {Event} @ignore */
		checkbox.checked = checked;
		let e;
		if ( typeof Event === 'function' ) {
			e = new Event( 'input', { bubbles: true, composed: true } );
		} else {
			e = document.createEvent( 'CustomEvent' );
			if ( !e ) return;
			e.initCustomEvent( 'input', true, false, false );
		}
		checkbox.dispatchEvent( e );
	}

	/**
	 * 当事件位于复选框、按钮和目标之外时，关闭目标。
	 * 通俗来讲就是单击其它位置时将关闭目标（通常是菜单）。
	 *
	 * @param {HTMLInputElement} checkbox
	 * @param {HTMLElement} button
	 * @param {Node} target
	 * @param {Event} event
	 * @return {void}
	 * @ignore
	 */
	function dismissIfExternalEventTarget( checkbox, button, target, event ) {
		const containsEventTarget = event.target instanceof Node && (
			checkbox.contains(event.target) || button.contains(event.target) || target.contains(event.target)
		);

		if (checkbox.checked && !containsEventTarget  ) {
			checkbox.checked = false;
			let e;
			if ( typeof Event === 'function' ) {
				e = new Event( 'input', { bubbles: true, composed: true } );
			} else {
				let e = document.createEvent( 'CustomEvent' );
				if ( !e ) return;
				e.initCustomEvent( 'input', true, false, false );
			}
			checkbox.dispatchEvent( e );
		}
	}

	function bindUpdateAriaExpandedOnInput( checkbox, button ) {
		const listener = updateAriaExpanded.bind(undefined, checkbox, button);
		// 每当复选框状态更改时，更新 aria-expanded 状态
		checkbox.addEventListener( 'input', listener );

		return function () {
			checkbox.removeEventListener( 'input', listener );
		};
	}

	function bindToggleOnClick( checkbox, button ) {
		function listener( event ) {
			// 不允许浏览器处理复选框
			event.preventDefault();
			setCheckedState( checkbox, !checkbox.checked );
		}
		button.addEventListener( 'click', listener, true );

		return function () {
			button.removeEventListener( 'click', listener, true );
		};
	}

	function bindToggleOnEnter( checkbox ) {
		function onKeyup( /** @type {KeyboardEvent} @ignore */ event ) {
			if ( event.key !== 'Enter' ) return;
			setCheckedState( checkbox, !checkbox.checked );
		}

		checkbox.addEventListener( 'keyup', onKeyup );
		return function () {
			checkbox.removeEventListener( 'keyup', onKeyup );
		};
	}

	/**
	 * 单击其它位置时关闭目标，并根据复选框状态（目标可见性）更新 aria-expanded 属性。
	 *
	 * @param {Window} window
	 * @param {HTMLInputElement} checkbox
	 * @param {HTMLElement} button
	 * @param {Node} target
	 * @return {function(): void} 清理函数，用于删除所添加的事件侦听器
	 * @ignore
	 */
	function bindDismissOnClickOutside( window, checkbox, button, target ) {
		const listener = dismissIfExternalEventTarget.bind(undefined, checkbox, button, target);
		window.addEventListener( 'click', listener, true );

		return function () {
			window.removeEventListener( 'click', listener, true );
		};
	}

	function bindDismissOnEscape( window, checkbox ) {
		const onKeyup = ( /** @type {KeyboardEvent} */ event ) => {
			if ( event.key !== 'Escape' ) return;
			setCheckedState( checkbox, false );
		};

		window.addEventListener( 'keyup', onKeyup, true );
		return function () {
			window.removeEventListener( 'keyup', onKeyup );
		};
	}

	function bindDismissOnFocusLoss( window, checkbox, button, target ) {
		// 如果聚焦目标之外的任意元素，则释放该目标
		// 最好为目标设置 focusout 侦听器，但显然其会干扰 click 侦听器
		const listener = dismissIfExternalEventTarget.bind(undefined, checkbox, button, target);
		window.addEventListener( 'focusin', listener, true );

		return function () {
			window.removeEventListener( 'focusin', listener, true );
		};
	}

	/**
	 * 单击链接时关闭目标，以防止导航到新页面时打开目标。
	 *
	 * @param {HTMLInputElement} checkbox
	 * @param {Node} target
	 * @return {function(): void} 清理函数，用于删除所添加的事件侦听器
	 * @ignore
	 */
	function bindDismissOnClickLink( checkbox, target ) {
		function dismissIfClickLinkEvent( event ) {
			// 处理对链接和链接子元素的点击
			if ( event.target.nodeName === 'A' || event.target.parentNode.nodeName === 'A' ) {
				setCheckedState( checkbox, false );
			}
		}
		target.addEventListener( 'click', dismissIfClickLinkEvent );

		return function () {
			target.removeEventListener( 'click', dismissIfClickLinkEvent );
		};
	}

	/**
	 * 单击或聚焦其它位置时，关闭目标，并根据用户所配置的复选框状态（目标可见性）更改更新 aria-expanded 属性。
	 * 单击按钮本身时，清除焦点轮廓。
	 *
	 * @param {Window} window
	 * @param {HTMLInputElement} checkbox	控制目标可见性的基础隐藏复选框
	 * @param {HTMLElement} button			与复选框关联的可见标签图标，此按钮负责切换基本复选框的状态
	 * @param {Node} target					要基于复选框状态切换其可见性的节点
	 * @return {function(): void}			清理函数，用于删除所添加的事件侦听器
	 * @ignore
	 */
	function bindCheckbox( window, checkbox, button, target ) {
		const cleanups = [
			bindUpdateAriaExpandedOnInput(checkbox),
			bindToggleOnClick(checkbox, button),
			bindToggleOnEnter(checkbox),
			bindDismissOnClickOutside(window, checkbox, button, target),
			bindDismissOnFocusLoss(window, checkbox, button, target),
			bindDismissOnClickLink(checkbox, target),
			bindDismissOnEscape( window, checkbox )
		];

		return function () {
			cleanups.forEach( function ( cleanup ) {
				cleanup();
			} );
		};
	}

	init();
}(window, document);