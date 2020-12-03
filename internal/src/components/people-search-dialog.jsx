require('./people-search-dialog.scss')

import React from 'react'
import PropTypes from 'prop-types'
import SearchField from './search-field'
import Button from './button'
import PeopleListItem  from './people-list-item'
import {apiGetUsersMatchingUsername} from '../util/api'


export default function PeopleSearchDialog({people, currentUserId, clearPeopleSearchResults, onSelectPerson, onClose, onSearchChange}){
	const [searchString, setSearchString] = React.useState('')
	// clear results on initial render
	React.useEffect(() => {
		clearPeopleSearchResults()
	}, [])

	// handle calling 2 prop methods when selecting
	const onClickPerson = React.useCallback(user => {
		onSelectPerson(user)
		onClose()
	}, [])

	return (
		<div className="people-search-dialog">
			<div className="wrapper">

				<h1 className="title">Find Users to Share With</h1>
				<div className="sub-title">People who can edit this module</div>
				<SearchField
					onChange={setSearchString}
					focusOnMount={true}
					placeholder="Search..."
					value={searchString}
				/>
			</div>
			<div className="access-list-wrapper">
				<ul className="access-list">
					{people.map(p => (
						<PeopleListItem key={p.id} isMe={p.id === currentUserId} {...p}>
							<Button className="select-button" onClick={() => onClickPerson(p)}>
								Select
							</Button>
						</PeopleListItem>
					))}
				</ul>
			</div>
		</div>
	)
}


PeopleSearchDialog.propTypes = {
	currentUserId: PropTypes.number,
	clearPeopleSearchResults: PropTypes.func,
	onSelectPerson: PropTypes.func,
	onClose: PropTypes.func,
	onSearchChange: PropTypes.func,
	people: PropTypes.arrayOf(PropTypes.shape({
		id: PropTypes.number.isRequired,
		avatarUrl: PropTypes.string,
		firstName: PropTypes.string,
		lastName: PropTypes.string,
		username: PropTypes.string
	}))
}


