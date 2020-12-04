require('./people-search-dialog.scss')

import React from 'react'
import PropTypes from 'prop-types'
import { useMutation, useQuery, useQueryCache } from 'react-query'
import SearchField from './search-field'
import Button from './button'
import PeopleListItem from './people-list-item'
import {
	apiGetUsersMatchingUsername,
	apiAddUsersToInstance,
	apiRemoveUsersFromInstance
} from '../util/api'
import LoadingIndicator from './loading-indicator'

let updateSearchStringIntervalID
const DEBOUNCE_INTERVAL_MS = 250

export default function PeopleSearchDialog({
	instID,
	usersWithAccess,
	currentUserId,
	clearPeopleSearchResults,
	onSelectPerson,
	onClose,
	onSearchChange
}) {
	const queryCache = useQueryCache()

	const [searchString, setSearchString] = React.useState('')
	const [apiSearchString, setAPISearchString] = React.useState('')
	const [isAdding, setIsAdding] = React.useState(false)

	// clear results on initial render
	React.useEffect(() => {
		clearPeopleSearchResults()
	}, [])

	const { isError, data, error, isFetching } = useQuery(
		['getUsersMatchingUsername', apiSearchString],
		apiGetUsersMatchingUsername,
		{
			initialStale: true,
			staleTime: Infinity,
			initialData: '',
			enabled: apiSearchString.length >= 3
		}
	)

	//people: [{id: 5, avatarUrl: '/assets/images/user-circle.svg', firstName: 'Demo', lastName: 'man', username: 'demoman'}]
	// {
	// 	userID: '1',
	// 	login: 'obojobo_admin',
	// 	first: 'Obojobo',
	// 	last: 'Admin',
	// 	mi: '',
	// 	email: 'mail@example.com',
	// 	createTime: '1134416800',
	// 	lastLogin: '1607033316',
	// 	_explicitType: 'rocketD\\auth\\User'
	// }
	const [mutateAddUsersToInstance] = useMutation(apiAddUsersToInstance)
	const [mutateRemoveUsersFromInstance] = useMutation(apiRemoveUsersFromInstance)

	const onClickAdd = React.useCallback(async user => {
		try {
			const resp = await mutateAddUsersToInstance(
				{ userIDs: [user.id], instID },
				{ throwOnError: true }
			)
			queryCache.invalidateQueries(['getInstancePerms', instID])

			return resp
		} catch (e) {
			console.error('Error setting extra attempts')
			console.error(e)
		}
	})

	const onClickRevoke = React.useCallback(async user => {
		if (user.id === currentUserId) {
			if (
				!confirm(
					'Are you sure you want to revoke your own access? This instance will no longer show up in your instance list.'
				)
			) {
				return
			}
		}
		try {
			const resp = await mutateRemoveUsersFromInstance(
				{ userIDs: [user.id], instID },
				{ throwOnError: true }
			)
			queryCache.invalidateQueries(['getInstancePerms', instID])

			return resp
		} catch (e) {
			console.error('Error setting extra attempts')
			console.error(e)
		}
	})

	const people = (data || []).map(user => ({
		id: user.userID,
		avatarUrl: '/assets/images/user-circle.svg',
		firstName: user.first,
		lastName: user.last,
		username: 'User #' + user.userID
	}))

	const usersWithAccess2 = usersWithAccess.map(user => ({
		id: user.userID,
		avatarUrl: '/assets/images/user-circle.svg',
		firstName: user.userName.first,
		lastName: user.userName.last,
		username: 'User #' + user.userID
	}))

	const userIDsWithAccess = usersWithAccess.map(user => user.userID).concat(currentUserId)

	return (
		<div className="people-search-dialog">
			<div className="wrapper">
				<h1 className="title">Users with access</h1>
				<div className="sub-title">People who can manage this instance</div>
			</div>

			<div className="access-list-wrapper">
				<ul className="access-list">
					{usersWithAccess2.map(p => (
						<PeopleListItem key={p.id} isMe={p.id === currentUserId} {...p}>
							<Button type="text" text="Revoke access" onClick={() => onClickRevoke(p)} />
						</PeopleListItem>
					))}
				</ul>
			</div>

			{isAdding ? (
				<div>
					<hr />
					<div className="wrapper">
						<h1 className="title">Find Users to Add:</h1>
						<div className="sub-title">
							Anyone you add will share the same access you do for this instance
						</div>
						<SearchField
							onChange={searchString => {
								setSearchString(searchString)

								if (searchString.length < 3) {
									setAPISearchString(searchString)
								} else {
									clearTimeout(updateSearchStringIntervalID)
									updateSearchStringIntervalID = setTimeout(() => {
										setAPISearchString(searchString)
									}, DEBOUNCE_INTERVAL_MS)
								}
							}}
							focusOnMount={true}
							placeholder="Search for a name..."
							value={searchString}
						/>
					</div>
					<div className="access-list-wrapper">
						<ul className="access-list">
							{people.map(p => {
								const isUserAlreadyAManager = userIDsWithAccess.indexOf(p.id) > -1

								return (
									<div className={isUserAlreadyAManager ? 'is-not-enabled' : 'is-enabled'}>
										<PeopleListItem key={p.id} isMe={p.id === currentUserId} {...p}>
											{isUserAlreadyAManager ? (
												<span className="already-has-access">
													{p.id === currentUserId
														? '(You already have access)'
														: '(Already has access)'}
												</span>
											) : (
												<Button type="text" text="+ Grant access" onClick={() => onClickAdd(p)} />
											)}
										</PeopleListItem>
									</div>
								)
							})}
						</ul>
						<LoadingIndicator
							isLoading={
								(searchString.length >= 3 && searchString !== apiSearchString && !data) ||
								isFetching
							}
						/>
					</div>
				</div>
			) : (
				<Button type="text" text="+ Add users..." onClick={() => setIsAdding(true)} />
			)}
		</div>
	)
}

PeopleSearchDialog.propTypes = {
	currentUserId: PropTypes.number,
	clearPeopleSearchResults: PropTypes.func,
	onSelectPerson: PropTypes.func,
	onClose: PropTypes.func,
	onSearchChange: PropTypes.func,
	people: PropTypes.arrayOf(
		PropTypes.shape({
			id: PropTypes.number.isRequired,
			avatarUrl: PropTypes.string,
			firstName: PropTypes.string,
			lastName: PropTypes.string,
			username: PropTypes.string
		})
	)
}
