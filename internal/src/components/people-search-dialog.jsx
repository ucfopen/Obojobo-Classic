require('./people-search-dialog.scss')

import React from 'react'
import PropTypes from 'prop-types'
import { useMutation, useQuery, useQueryCache } from 'react-query'
import SearchField from './search-field'
import Button from './button'
import PeopleListItem from './people-list-item'
import {
	getUsersMatchingSearch,
	apiAddUsersToInstance,
	apiRemoveUsersFromInstance
} from '../util/api'
import RepositoryModal from './repository-modal'
import LoadingIndicator from './loading-indicator'

let updateSearchStringIntervalID
const DEBOUNCE_INTERVAL_MS = 250

export default function PeopleSearchDialog({
	instID,
	usersWithAccess,
	currentUserId,
	onClose,
	instanceName
}) {
	const queryCache = useQueryCache()
	const [searchString, setSearchString] = React.useState('')
	const [apiSearchString, setAPISearchString] = React.useState('')
	const [isAdding, setIsAdding] = React.useState(false)

	const { data, isFetching } = useQuery(
		['getUsersMatchingSearch', apiSearchString],
		getUsersMatchingSearch,
		{
			cacheTime: 10000,
			initialStale: true,
			staleTime: Infinity,
			initialData: '',
			enabled: apiSearchString.length >= 3
		}
	)

	const [mutateAddUsersToInstance] = useMutation(apiAddUsersToInstance)
	const [mutateRemoveUsersFromInstance] = useMutation(apiRemoveUsersFromInstance)

	const onClickAdd = React.useCallback(
		async user => {
			try {
				const resp = await mutateAddUsersToInstance(
					{ userIDs: [user.id], instID },
					{ throwOnError: true }
				)
				queryCache.invalidateQueries(['getInstancePerms', instID])

				return resp
			} catch (e) {
				console.error('Error setting extra attempts') // eslint-disable-line no-console
				console.error(e) // eslint-disable-line no-console
			}
		},
		[instID]
	)

	const onClickRevoke = React.useCallback(
		async user => {
			if (user.id === currentUserId) {
				// eslint-disable-next-line no-alert
				const revoke = window.confirm(
					'Are you sure you want to revoke your own access? This instance will no longer show up in your instance list.'
				)
				if (!revoke) return
			}

			try {
				const resp = await mutateRemoveUsersFromInstance(
					{ userIDs: [user.id], instID },
					{ throwOnError: true }
				)
				queryCache.invalidateQueries(['getInstancePerms', instID])

				return resp
			} catch (e) {
				console.error('Error setting extra attempts') // eslint-disable-line no-console
				console.error(e) // eslint-disable-line no-console
			}
		},
		[instID]
	)

	const people = React.useMemo(() => {
		if (!data) return []
		return data.map(user => ({
			id: user.userID,
			firstName: user.first,
			lastName: user.last,
			username: user.login
		}))
	}, [data])

	const usersWithAccess2 = usersWithAccess.map(user => ({
		id: user.userID,
		firstName: user.userName.first,
		lastName: user.userName.last,
		username: user.userName.login
	}))

	const userIDsWithAccess = usersWithAccess.map(user => user.userID).concat(currentUserId)

	return (
		<RepositoryModal className="peopleSearch" instanceName={instanceName} onCloseModal={onClose}>
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
										<div
											key={p.id}
											className={isUserAlreadyAManager ? 'is-not-enabled' : 'is-enabled'}
										>
											<PeopleListItem isMe={p.id === currentUserId} {...p}>
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
		</RepositoryModal>
	)
}

PeopleSearchDialog.propTypes = {
	currentUserId: PropTypes.number,
	onClose: PropTypes.func,
	people: PropTypes.arrayOf(
		PropTypes.shape({
			id: PropTypes.number.isRequired,
			firstName: PropTypes.string,
			lastName: PropTypes.string,
			username: PropTypes.string
		})
	)
}
