require('./avatar.scss')

import React from 'react'
import UserCircle from '../../../assets/images/user-circle.svg'

export default function Avatar({className, avatarUrl, alt = '', notice}){
	return (
		<div className={`avatar ${className || ''}`}>
			<div className="avatar--image">
				<UserCircle height="100%" width="100%" />
			</div>
			{notice ? <div className="avatar--notice">{notice}</div> : null}
		</div>
	)
}

