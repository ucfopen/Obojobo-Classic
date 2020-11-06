import './help-button.scss'

import React from 'react'

class MoreInfoButton extends React.Component {
	constructor() {
		super()

		this.boundOnMouseOver = this.onMouseOver.bind(this)
		this.boundOnMouseOut = this.onMouseOut.bind(this)
		this.boundOnClick = this.onClick.bind(this)
		this.hide = this.hide.bind(this)

		this.state = {
			mode: 'hidden'
		}

		this.dialogRef = React.createRef()
	}

	hide() {
		this.setState({ mode: 'hidden' })
	}

	onMouseOver() {
		if (this.state.mode === 'hidden') {
			this.setState({ mode: 'hover' })
		}
	}

	onMouseOut() {
		if (this.state.mode === 'hover') {
			this.hide()
		}
	}

	onClick() {
		if (this.state.mode === 'clicked') {
			this.hide()
		} else {
			this.setState({ mode: 'clicked' })
		}
	}

	componentDidUpdate() {
		if (this.state.mode === 'clicked') {
			this.dialogRef.current.focus()
		}
	}

	render() {
		const isShowing = this.state.mode === 'hover' || this.state.mode === 'clicked'

		return (
			<div className={`repository--help-button is-mode-${this.state.mode}`}>
				<button
					type="button" // Prevents click event when inside a <form>
					onMouseOver={this.boundOnMouseOver}
					onMouseOut={this.boundOnMouseOut}
					onClick={this.boundOnClick}
					aria-label={this.props.ariaLabel || 'More info'}
				>
					?
				</button>
				{isShowing ? (
					<div
						className="info"
						role="dialog"
						tabIndex="-1"
						onBlur={this.hide}
						ref={this.dialogRef}
						aria-labelledby="repository--help-button--container"
					>
						<div id="repository--help-button--container" className="container">
							{this.props.children}
						</div>
					</div>
				) : null}
			</div>
		)
	}
}

export default MoreInfoButton
