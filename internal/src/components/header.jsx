import './header.scss'

import React from 'react'
import PropTypes from 'prop-types'
import ModalAboutObojoboNext from './modal-about-obojobo-next'
import { apiLogout } from '../util/api'
import useToggleState from '../hooks/use-toggle-state'

export default function Header({ userName }) {
	const [aboutVisible, hideAbout, showAbout] = useToggleState()

	const onClickLogOut = React.useCallback(async () => {
		await apiLogout()
		window.location.reload(false)
	}, [])

	return (
		<div className="obo-header">
			<div className="wrapper">
				<div className="obo-header--left-side">
					<img className="obo-classic-logo" src={'./assets/images/viewer/obojobo-logo.svg'} />
				</div>

				<div className="banner-header">
					<span>What&apos;s different?</span>
					<button className="banner-header--modal-button" onClick={showAbout}>
						Click here to find out about the new look and our new version
					</button>
				</div>

				<div className="obo-header--right-side">
					<p>{userName}</p>
					<button className="header-btn" onClick={onClickLogOut}>
						Logout
					</button>
				</div>
			</div>
			{aboutVisible
				? <ModalAboutObojoboNext
					onClose={hideAbout}
					/>
				: null
			}
		</div>
	)
}

Header.propTypes = {
	userName: PropTypes.string.isRequired,
}
