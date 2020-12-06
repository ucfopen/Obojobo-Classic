require('./people-list-item.scss')

import React from 'react'
import PropTypes from 'prop-types'
import Avatar from './avatar'

const PeopleListItem = ({ avatarUrl, firstName, lastName, isMe, username, children }) => (
	<li className="people-list-item">
		<Avatar avatarUrl={avatarUrl} />
		<div className="user-info">
			<div className="user-name">
				{`${firstName} ${lastName}`} {isMe ? <i>(me)</i> : null}
			</div>
			<div className="user-username">{username}</div>
		</div>
		{children}
	</li>
)

PeopleListItem.defaultProps = {
	firstName: '',
	lastName: '',
	isMe: false
}

PeopleListItem.propTypes = {
	isMe: PropTypes.bool,
	id: PropTypes.number.isRequired,
	avatarUrl: PropTypes.string,
	firstName: PropTypes.string,
	lastName: PropTypes.string,
	username: PropTypes.string
}

export default PeopleListItem
