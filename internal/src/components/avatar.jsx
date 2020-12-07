require('./avatar.scss')

import React from 'react'
import UserCircle from '../../../assets/images/user-circle.svg'

export default function Avatar({ className }) {
	return (
		<div className={`avatar ${className || ''}`}>
			<div className="avatar--image">
				<UserCircle height="100%" width="100%" />
			</div>
		</div>
	)
}
