/**
 * Copyright 2022 Design Barn Inc.
 */

import { gql } from 'urql';

export const fragments = {
  pageInfoFragment: gql`
    fragment PageFields on PageInfo {
      startCursor
      endCursor
      hasNextPage
      hasPreviousPage
    }
  `,
  projectFragment: gql`
    fragment ProjectFields on Project {
      id
      title
      isPrivate
    }
  `,
  fileFragment: gql`
    fragment FileFields on File {
      id
      name
      currentVersionId
      backgroundColor
      status
      fileObject {
        url
        key
        thumbnails {
          png {
            small {
              url
            }
          }
        }
      }
      updatedAt
      createdBy {
        name
      }
      modifiedBy {
        name
      }
    }
  `,
  fileVersionFragment: gql`
    fragment FileVersionFields on FileVersion {
      id
      name
      versionNumber
      fileVersionId
      fileObject {
        url
      }
    }
  `,

  folderFragment: gql`
    fragment FolderFields on Folder {
      filesCount
      id
      name
      thumbnails {
        thumbnails {
          png {
            small {
              url
            }
          }
        }
      }
      modifiedBy {
        firstName
        lastName
      }
      createdBy {
        firstName
        lastName
      }
      updatedAt
    }
  `,
  publicAnimFragment: gql`
    fragment PublicAnimFields on PublicAnimationConnection {
      pageInfo {
        startCursor
        endCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
      edges {
        node {
          id
          lottieUrl
          bgColor
          createdBy {
            firstName
            lastName
            avatarUrl
          }
        }
      }
    }
  `,
};

export const queries = {
  hasAccessToAWorkspace: gql`
    query hasAccessToAWorkspace {
      hasAccessToAWorkspace
    }
  `,
  softwareUpdates: gql`
    query softwareUpdates($version: String!) {
      softwareUpdates(app: "wordpress", version: $version) {
        infoUrl
        version
        changelog
        autoUpdate
        forceUpdate
        downloadUrl
      }
    }
  `,
  project: gql`
    query project($id: ID!) {
      project(id: $id) {
        id
        title
      }
    }
  `,

  projectFiles: gql`
  query projectFiles(
    $id: ID!
    $first: Float
    $after: String
    $last: Float
    $before: String
    $orderBy: QuerySortOptions
    $status: String
    $type: String
  ) {
    projectFiles(
      id: $id
      first: $first
      after: $after
      last: $last
      before: $before
      orderBy: $orderBy
      status: $status
      type: $type
    ) {
      pageInfo {
        startCursor
        endCursor
        hasNextPage
        hasPreviousPage
      }
      edges {
        node {
          ... on File {
            ...FileFields
          }
          ... on Folder {
            ...FolderFields
          }
        }
      }
    }
    ${fragments.fileFragment}
    ${fragments.folderFragment}
  }
`,
  folder: gql`
    query folder($id: ID!) {
      folder(id: $id) {
        id
        name
        project {
          id
          title
          isPrivate
        }
      }
    }
  `,
  fileVersions: gql`
    query fileVersions(
      $fileId: ID!
      $first: Float
      $after: String
      $last: Float
      $before: String
      $orderBy: QuerySortOptions
    ) {
      fileVersions(
        fileId: $fileId
        first: $first
        after: $after
        last: $last
        before: $before
        orderBy: $orderBy
      ) {
        pageInfo {
          startCursor
          endCursor
          hasNextPage
          hasPreviousPage
        }
        edges {
          node {
            ... on FileVersion {
              ...FileVersionFields
            }
          }
        }
        totalCount
      }
      ${fragments.fileVersionFragment}
    }
  `,
  folderFiles: gql`
    query folderFiles(
      $id: ID!
      $first: Float
      $after: String
      $last: Float
      $before: String
      $orderBy: QuerySortOptions
      $status: String
    ) {
      folderFiles(
        id: $id
        first: $first
        after: $after
        last: $last
        before: $before
        orderBy: $orderBy
        status: $status
      ) {
        pageInfo {
          startCursor
          endCursor
          hasNextPage
          hasPreviousPage
        }
        edges {
          node {
            ... on File {
              ...FileFields
            }
          }
        }
        totalCount
      }
      ${fragments.fileFragment}
    }
  `,

  currentWorkspace: gql`
    query currentWorkspace {
      currentWorkspace {
        id
        name
        icon
        features {
          name
          slug
          max
          isEnabled
          currentCount
        }
      }
    }
  `,
  animation: gql`
    query animation($id: String!) {
      animation(id: $id) {
        id
        name
        status
        fileObject {
          key
          url
        }
        folder {
          id
          name
        }
        project {
          id
          title
          isPrivate
        }
      }
    }
  `,
  myWorkspaces: gql`
    query workspaces {
      workspaces {
        id
        name
        icon
      }
    }
  `,
  searchBkp: gql`
    query search($page: Float, $pageSize: Float, $query: String!) {
      search(page: $page, pageSize: $pageSize, query: $query) {
        query
        currentPage
        perPage
        totalPages
        from
        to
        total
        results {
          id
          lottieUrl
          bgColor
          createdBy {
            name
            avatarUrl
          }
        }
      }
    }
  `,
  search: gql`
  query searchPublicAnimations($first: Int, $last: Int, $after: String, $before: String, $query: String!) {
    searchPublicAnimations(first: $first, last: $last, after: $after, before: $before, query: $query) {
      ...PublicAnimFields
  }
  ${fragments.publicAnimFragment}
}
`,
  popular: gql`
  query popularPublicAnimations($first: Int, $after: String, $before: String, $last: Int) {
    popularPublicAnimations(first: $first, after: $after, before: $before, last: $last) {
      ...PublicAnimFields
  }
  ${fragments.publicAnimFragment}
}
`,
  featured: gql`
  query featuredPublicAnimations($first: Int, $after: String, $before: String, $last: Int) {
    featuredPublicAnimations(first: $first, after: $after, before: $before, last: $last) {
      ...PublicAnimFields
  }
  ${fragments.publicAnimFragment}
}
`,
  recent: gql`
      query recentPublicAnimations($first: Int, $after: String, $before: String, $last: Int) {
        recentPublicAnimations(first: $first, after: $after, before: $before, last: $last) {
          ...PublicAnimFields
      }
      ${fragments.publicAnimFragment}
    }
`,
  viewer: gql`
    query viewer {
      viewer {
        id
        name
        email
        avatarUrl
        username
      }
    }
  `,
  workspaceProjects: gql`
    query workspaceProjects($workspaceId: ID!) {
      workspaceProjects(workspaceId: $workspaceId, first: 15) {
        pageInfo {
          startCursor
          endCursor
          hasNextPage
          hasPreviousPage
        }
        totalCount
        edges {
          node {
            id
            title
            isPrivate
            filesCount
          }
        }
      }
    }
  `,
  searchWorkspace: gql`
    query searchWorkspace(
      $query: String
      $first: Float
      $after: String
      $before: String
      $inclFiles: Boolean!
      $inclFolders: Boolean!
      $inclProjects: Boolean!
    ) {
      searchWorkspace(query: $query, first: $first, after: $after, before: $before) {
        files @include(if: $inclFiles) {
          totalCount
          pageInfo {
            ...PageFields
          }
          edges {
            node {
              ...FileFields
            }
          }
        }
        folders @include(if: $inclFolders) {
          totalCount
          pageInfo {
            ...PageFields
          }
          totalCount
          edges {
            node {
              ...FolderFields
            }
          }
        }
        projects @include(if: $inclProjects) {
          totalCount
          pageInfo {
            ...PageFields
          }
          totalCount
          edges {
            node {
              ...ProjectFields
            }
          }
        }
      }
      ${fragments.fileFragment}
      ${fragments.folderFragment}
      ${fragments.projectFragment}
      ${fragments.pageInfoFragment}

    }
  `,
};

export const mutation = {
  setupInitialWorkspace: gql`
    mutation setupInitialWorkspace {
      setupInitialWorkspace {
        id
      }
    }
  `,
  updateCurrentWorkspace: gql`
    mutation updateCurrentWorkspace($workspaceId: ID!) {
      updateCurrentWorkspace(workspaceId: $workspaceId) {
        id
      }
    }
  `,
  createLoginToken: gql`
    mutation createLoginToken($appKey: String) {
      createLoginToken(appKey: $appKey) {
        token
        loginUrl
      }
    }
  `,
  tokenLogin: gql`
    mutation tokenLogin($token: String!) {
      tokenLogin(token: $token) {
        accessToken
        tokenType
      }
    }
  `,
  createHitCountEvent: gql`
    mutation hitCountEventCreate($source: Float, $userId: ID, $visitorId: ID, $resourceId: Float!, $method: Float!) {
      hitCountEventCreate(
        input: { source: $source, userId: $userId, visitorId: $visitorId, method: $method }
        resourceId: $resourceId
      ) {
        status
        message
      }
    }
  `,
};
