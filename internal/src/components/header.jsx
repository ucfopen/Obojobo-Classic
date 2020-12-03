import './header.scss'

import React from 'react'
import PropTypes from 'prop-types'

export default function Header({ userName, onClickBanner, onClickLogOut }) {
	return (
		<div className="obo-header">
			<div className="wrapper">
				<div className="obo-header--left-side">
					<img className="obo-classic-logo" src={'./assets/images/viewer/obojobo-logo.svg'} />
				</div>

				<div className="banner-header">
					<span>What&apos;s different?</span>
					<button className="banner-header--modal-button" onClick={e => onClickBanner(e)}>
						Click here to find out about the new look and our new version
					</button>
				</div>

				<div className="obo-header--right-side">
					<p>{userName}</p>
					<button className="header-btn" onClick={e => onClickLogOut(e)}>
						Logout
					</button>
				</div>
			</div>
		</div>
	)
}

Header.propTypes = {
	userName: PropTypes.string.isRequired,
	onClickBanner: PropTypes.func.isRequired,
	onClickLogOut: PropTypes.func.isRequired
}
